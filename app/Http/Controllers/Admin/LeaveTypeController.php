<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::orderBy('name')->get();
        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100|unique:leave_types,name',
            'max_days' => 'required|integer|min:1|max:365',
        ]);

        LeaveType::create($request->only('name', 'max_days'));

        return back()->with('success', 'Leave type added successfully.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'name'     => 'required|string|max:100|unique:leave_types,name,' . $leaveType->id,
            'max_days' => 'required|integer|min:1|max:365',
        ]);

        $leaveType->update($request->only('name', 'max_days'));

        return back()->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->leaveRequests()->exists()) {
            return back()->with('error', 'Cannot delete a leave type that has existing requests.');
        }

        $leaveType->delete();

        return back()->with('success', 'Leave type deleted.');
    }
}
