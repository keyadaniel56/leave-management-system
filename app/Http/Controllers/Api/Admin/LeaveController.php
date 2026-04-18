<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\LeaveRequestReviewed;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LeaveController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/api/admin/leaves',
        summary: 'Get all leave requests',
        security: [['bearerAuth' => []]],
        tags: ['Admin - Leaves'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected', 'all'])
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Leave requests retrieved'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $validStatuses = ['pending', 'approved', 'rejected', 'all'];
        if (!in_array($status, $validStatuses)) {
            $status = 'pending';
        }

        $leaves = LeaveRequest::with(['user', 'leaveType'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        return $this->success($leaves, 'Leave requests retrieved.');
    }

    public function show(LeaveRequest $leave)
    {
        return $this->success(
            $leave->load(['user', 'leaveType', 'reviewer']),
            'Leave request retrieved.'
        );
    }

    #[OA\Post(
        path: '/api/admin/leaves/{id}/approve',
        summary: 'Approve a leave request',
        security: [['bearerAuth' => []]],
        tags: ['Admin - Leaves'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'admin_note', type: 'string', example: 'Approved.')]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Leave request approved'),
            new OA\Response(response: 422, description: 'Already reviewed'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function approve(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return $this->error('This request has already been reviewed.', 422);
        }

        $leave->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note'  => $request->input('admin_note'),
        ]);

        // Deduct from leave balance
        LeaveBalance::where('user_id', $leave->user_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', $leave->start_date->year)
            ->increment('used_days', $leave->total_days);

        $leave->load('leaveType', 'user');
        broadcast(new LeaveRequestReviewed($leave));

        return $this->success($leave, 'Leave request approved.');
    }

    #[OA\Post(
        path: '/api/admin/leaves/{id}/reject',
        summary: 'Reject a leave request',
        security: [['bearerAuth' => []]],
        tags: ['Admin - Leaves'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'admin_note', type: 'string', example: 'Not enough coverage.')]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Leave request rejected'),
            new OA\Response(response: 422, description: 'Already reviewed'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function reject(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return $this->error('This request has already been reviewed.', 422);
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $leave->update([
            'status'      => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note'  => $request->input('admin_note'),
        ]);

        $leave->load('leaveType', 'user');
        broadcast(new LeaveRequestReviewed($leave));

        return $this->success($leave, 'Leave request rejected.');
    }
}
