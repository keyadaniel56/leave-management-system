<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;

class LeaveBalanceService
{
    public function seedBalancesForUser(User $user, int $year = null): void
    {
        $year = $year ?? now()->year;

        LeaveType::all()->each(function ($type) use ($user, $year) {
            LeaveBalance::firstOrCreate(
                [
                    'user_id'       => $user->id,
                    'leave_type_id' => $type->id,
                    'year'          => $year,
                ],
                [
                    'total_days' => $type->max_days,
                    'used_days'  => 0,
                ]
            );
        });
    }
}
