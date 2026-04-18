<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(LeaveType::orderBy('name')->get(), 'Leave types retrieved.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100|unique:leave_types,name',
            'max_days' => 'required|integer|min:1|max:365',
        ]);

        $leaveType = LeaveType::create($request->only('name', 'max_days'));

        return $this->success($leaveType, 'Leave type created.', 201);
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'name'     => 'required|string|max:100|unique:leave_types,name,' . $leaveType->id,
            'max_days' => 'required|integer|min:1|max:365',
        ]);

        $leaveType->update($request->only('name', 'max_days'));

        return $this->success($leaveType, 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->leaveRequests()->exists()) {
            return $this->error('Cannot delete a leave type that has existing requests.', 422);
        }

        $leaveType->delete();

        return $this->success(null, 'Leave type deleted.');
    }
}
