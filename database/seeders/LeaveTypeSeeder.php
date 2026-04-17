<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Annual Leave',    'max_days' => 21],
            ['name' => 'Sick Leave',      'max_days' => 14],
            ['name' => 'Maternity Leave', 'max_days' => 90],
            ['name' => 'Paternity Leave', 'max_days' => 14],
            ['name' => 'Unpaid Leave',    'max_days' => 30],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
