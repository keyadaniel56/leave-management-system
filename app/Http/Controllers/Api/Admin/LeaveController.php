<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\LeaveRequestReviewed;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

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

        $leave->load('leaveType', 'user');
        broadcast(new LeaveRequestReviewed($leave));

        return $this->success($leave, 'Leave request approved.');
    }

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
