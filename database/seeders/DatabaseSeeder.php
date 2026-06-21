<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Production: use ProductionSeeder instead
        //   php artisan db:seed --class=ProductionSeeder
        //
        // Demo/dev only:
        //   php artisan db:seed --class=DemoDataSeeder
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            LeaveTypeSeeder::class,
            MaturitySeeder::class,
            CompanySettingsSeeder::class,
        ]);
    }
}
