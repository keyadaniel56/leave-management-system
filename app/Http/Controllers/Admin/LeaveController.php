<?php

namespace App\Http\Controllers\Admin;

use App\Events\LeaveRequestReviewed;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $leaves = LeaveRequest::with(['user', 'leaveType'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        return view('admin.leaves.index', compact('leaves', 'status'));
    }

    public function show(LeaveRequest $leave)
    {
        $leave->load(['user', 'leaveType', 'reviewer']);
        return view('admin.leaves.show', compact('leave'));
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $leave->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note'  => $request->input('admin_note'),
        ]);

        $leave->load('leaveType', 'user');
        broadcast(new LeaveRequestReviewed($leave));

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
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

        return back()->with('success', 'Leave request rejected.');
    }
}
