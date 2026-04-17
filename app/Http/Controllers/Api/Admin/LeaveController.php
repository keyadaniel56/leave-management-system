<?php

namespace App\Http\Controllers\Api\Admin;

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

        return response()->json($leaves);
    }

    public function show(LeaveRequest $leave)
    {
        return response()->json($leave->load(['user', 'leaveType', 'reviewer']));
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return response()->json(['message' => 'This request has already been reviewed.'], 422);
        }

        $leave->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note'  => $request->input('admin_note'),
        ]);

        return response()->json(['message' => 'Leave request approved.', 'leave' => $leave]);
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return response()->json(['message' => 'This request has already been reviewed.'], 422);
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

        return response()->json(['message' => 'Leave request rejected.', 'leave' => $leave]);
    }
}
