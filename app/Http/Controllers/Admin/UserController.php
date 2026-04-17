<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'employee')
            ->withCount('leaveRequests')
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $leaves = $user->leaveRequests()->with('leaveType')->latest()->paginate(10);
        return view('admin.users.show', compact('user', 'leaves'));
    }
}
