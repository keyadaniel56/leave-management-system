<?php

namespace App\Http\Controllers\Api;

use App\Events\LeaveRequestSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LeaveController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/api/leaves',
        summary: 'Get my leave requests',
        security: [['bearerAuth' => []]],
        tags: ['Leaves'],
        responses: [
            new OA\Response(response: 200, description: 'Leave requests retrieved'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request)
    {
        $leaves = $request->user()
            ->leaveRequests()
            ->with('leaveType')
            ->latest()
            ->paginate(10);

        return $this->success($leaves, 'Leave requests retrieved.');
    }

    #[OA\Post(
        path: '/api/leaves',
        summary: 'Submit a leave request',
        security: [['bearerAuth' => []]],
        tags: ['Leaves'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['leave_type_id', 'start_date', 'end_date', 'reason'],
                properties: [
                    new OA\Property(property: 'leave_type_id', type: 'integer', example: 1),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-05-01'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2026-05-05'),
                    new OA\Property(property: 'reason', type: 'string', example: 'Family vacation'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Leave request submitted'),
            new OA\Response(response: 422, description: 'Validation error or insufficient balance'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|max:500',
        ]);

        $totalDays = (int) now()->parse($validated['start_date'])
            ->diffInWeekdays(now()->parse($validated['end_date'])) + 1;

        // Check leave balance
        $balance = LeaveBalance::where('user_id', $request->user()->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', now()->year)
            ->first();

        if (!$balance || !$balance->hasEnoughDays($totalDays)) {
            return $this->error('Insufficient leave balance for the requested days.', 422);
        }

        $leave = $request->user()->leaveRequests()->create([
            ...$validated,
            'total_days' => $totalDays,
            'status'     => 'pending',
        ]);

        $leave->load('leaveType', 'user');
        broadcast(new LeaveRequestSubmitted($leave))->toOthers();

        return $this->success($leave, 'Leave request submitted.', 201);
    }

    public function show(Request $request, LeaveRequest $leave)
    {
        if ($leave->user_id !== $request->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        return $this->success($leave->load('leaveType'), 'Leave request retrieved.');
    }

    #[OA\Delete(
        path: '/api/leaves/{id}',
        summary: 'Cancel a pending leave request',
        security: [['bearerAuth' => []]],
        tags: ['Leaves'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Leave request cancelled'),
            new OA\Response(response: 422, description: 'Cannot cancel non-pending request'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Request $request, LeaveRequest $leave)
    {
        if ($leave->user_id !== $request->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        if ($leave->status !== 'pending') {
            return $this->error('Only pending requests can be cancelled.', 422);
        }

        $leave->delete();

        return $this->success(null, 'Leave request cancelled.');
    }

    public function leaveTypes()
    {
        return $this->success(LeaveType::orderBy('name')->get(), 'Leave types retrieved.');
    }

    #[OA\Get(
        path: '/api/leave-balance',
        summary: 'Get my leave balance for current year',
        security: [['bearerAuth' => []]],
        tags: ['Leaves'],
        responses: [
            new OA\Response(response: 200, description: 'Leave balances retrieved'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function balance(Request $request)
    {
        $balances = LeaveBalance::where('user_id', $request->user()->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->get()
            ->map(fn($b) => [
                'leave_type'     => $b->leaveType->name,
                'total_days'     => $b->total_days,
                'used_days'      => $b->used_days,
                'remaining_days' => $b->remaining_days,
            ]);

        return $this->success($balances, 'Leave balances retrieved.');
    }
}
