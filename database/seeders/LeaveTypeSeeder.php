<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Default leave types based on Tanzania's Employment and Labour
     * Relations Act. Adjust max_days_per_year to match your company's
     * HR policy / employment contracts where they exceed the statutory
     * minimum.
     */
    public function run(): void
    {
        $leaveTypes = [
            ['name' => 'Annual Leave', 'max_days_per_year' => 28, 'is_paid' => true],
            ['name' => 'Sick Leave', 'max_days_per_year' => 126, 'is_paid' => true], // 63 days full pay + 63 days half pay per ELRA
            ['name' => 'Maternity Leave', 'max_days_per_year' => 84, 'is_paid' => true],
            ['name' => 'Paternity Leave', 'max_days_per_year' => 3, 'is_paid' => true],
            ['name' => 'Compassionate Leave', 'max_days_per_year' => 4, 'is_paid' => true],
            ['name' => 'Unpaid Leave', 'max_days_per_year' => 0, 'is_paid' => false],
            ['name' => 'Study Leave', 'max_days_per_year' => 0, 'is_paid' => false],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
