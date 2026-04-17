<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $leaves = $request->user()
            ->leaveRequests()
            ->with('leaveType')
            ->latest()
            ->paginate(10);

        return view('leave.index', compact('leaves'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::all();
        return view('leave.create', compact('leaveTypes'));
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

        $request->user()->leaveRequests()->create([
            ...$validated,
            'total_days' => $totalDays,
            'status'     => 'pending',
        ]);

        return redirect()->route('leave.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function destroy(Request $request, LeaveRequest $leave)
    {
        if ($leave->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $leave->delete();

        return back()->with('success', 'Leave request cancelled.');
    }
}
