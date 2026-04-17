<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $stats = [
                'total'    => LeaveRequest::count(),
                'pending'  => LeaveRequest::where('status', 'pending')->count(),
                'approved' => LeaveRequest::where('status', 'approved')->count(),
                'rejected' => LeaveRequest::where('status', 'rejected')->count(),
                'employees' => User::where('role', 'employee')->count(),
            ];
            return view('dashboard.admin', compact('stats'));
        }

        $stats = [
            'total'    => $user->leaveRequests()->count(),
            'pending'  => $user->leaveRequests()->where('status', 'pending')->count(),
            'approved' => $user->leaveRequests()->where('status', 'approved')->count(),
            'rejected' => $user->leaveRequests()->where('status', 'rejected')->count(),
        ];

        $recent = $user->leaveRequests()->with('leaveType')->latest()->take(5)->get();

        return view('dashboard.employee', compact('stats', 'recent'));
    }
}
