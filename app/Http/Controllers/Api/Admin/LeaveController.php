<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\LeaveRequestReviewed;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/admin/leaves",
     *     tags={"Admin - Leaves"},
     *     summary="Get all leave requests",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false,
     *         @OA\Schema(type="string", enum={"pending","approved","rejected","all"})
     *     ),
     *     @OA\Response(response=200, description="Leave requests retrieved"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/admin/leaves/{id}/approve",
     *     tags={"Admin - Leaves"},
     *     summary="Approve a leave request",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="admin_note", type="string", example="Approved. Enjoy your leave.")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Leave request approved"),
     *     @OA\Response(response=422, description="Already reviewed"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/admin/leaves/{id}/reject",
     *     tags={"Admin - Leaves"},
     *     summary="Reject a leave request",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="admin_note", type="string", example="Not enough team coverage.")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Leave request rejected"),
     *     @OA\Response(response=422, description="Already reviewed"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
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
