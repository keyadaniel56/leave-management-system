<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        return response()->json($leaves);
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

        return response()->json($leave->load('leaveType'), 201);
    }

    public function show(Request $request, LeaveRequest $leave)
    {
        if ($leave->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($leave->load('leaveType'));
    }

    public function destroy(Request $request, LeaveRequest $leave)
    {
        if ($leave->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($leave->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be cancelled.'], 422);
        }

        $leave->delete();

        return response()->json(['message' => 'Leave request cancelled.']);
    }

    public function leaveTypes()
    {
        return response()->json(LeaveType::all());
    }
}
