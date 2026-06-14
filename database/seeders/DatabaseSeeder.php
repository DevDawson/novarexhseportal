<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,       // Roles & permissions (must run first)
            LeaveTypeSeeder::class,  // Default Tanzania leave types
            DemoDataSeeder::class,   // Departments, users, clients, projects, etc.
            ChartOfAccountsSeeder::class,   // ChartOfAccountsSeeder
        ]);
    }
}
