<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Run on a fresh production database:
 *   php artisan db:seed --class=ProductionSeeder
 *
 * Safe to re-run (all methods use updateOrCreate / findOrCreate).
 * Does NOT create demo data.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,           // 1. Roles + 51 permissions (must be first)
            AdminUserSeeder::class,      // 2. MD user (jmakunga1@gmail.com)
            LeaveTypeSeeder::class,      // 3. Tanzania leave types (Annual, Sick, Maternity, etc.)
            MaturitySeeder::class,       // 4. HSE Maturity Index dimensions & indicators (9 dims, 27 indicators)
            CompanySettingsSeeder::class,// 5. Company name, address, email defaults
        ]);
    }
}
