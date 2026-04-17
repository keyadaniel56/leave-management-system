<?php

namespace App\Http\Controllers\Api;

use App\Events\LeaveRequestSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $leaves = $request->user()
            ->leaveRequests()
            ->with('leaveType')
            ->latest()
            ->paginate(10);

        return $this->success($leaves, 'Leave requests retrieved.');
    }

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

        $leave = $request->user()->leaveRequests()->create([
            ...$validated,
            'total_days' => $totalDays,
            'status'     => 'pending',
        ]);

        $leave->load('leaveType', 'user');
        broadcast(new LeaveRequestSubmitted($leave))->toOthers();

        return $this->success($leave->load('leaveType'), 'Leave request submitted.', 201);
    }

    public function show(Request $request, LeaveRequest $leave)
    {
        if ($leave->user_id !== $request->user()->id) {
            return $this->error('Forbidden.', 403);
        }

        return $this->success($leave->load('leaveType'), 'Leave request retrieved.');
    }

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
        return $this->success(LeaveType::all(), 'Leave types retrieved.');
    }
}
