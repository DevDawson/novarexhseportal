<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expand employment_type enum to include Part-Time and Consultant
        //    (keeps existing 'contract' and 'intern' values for flexibility).
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE staff MODIFY COLUMN employment_type ENUM(
                'permanent',
                'part_time',
                'casual',
                'consultant',
                'contract',
                'intern'
            ) NOT NULL DEFAULT 'permanent'");
        }

        // 2. Add rate fields used by the Payroll Calculation Engine
        //    depending on employment type.
        Schema::table('staff', function (Blueprint $table) {
            $table->decimal('hourly_rate', 15, 2)->nullable()->after('basic_salary')
                ->comment('Used for Part-Time employees: Gross Pay = Hours Worked x Hourly Rate');

            $table->decimal('daily_rate', 15, 2)->nullable()->after('hourly_rate')
                ->comment('Used for Casual employees: Gross Pay = Days Worked x Daily Rate');

            $table->decimal('contract_amount', 15, 2)->nullable()->after('daily_rate')
                ->comment('Used for Consultants: Gross Payment = Contract Amount (per period)');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['hourly_rate', 'daily_rate', 'contract_amount']);
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            // Revert any new-type rows to 'permanent' before shrinking the enum.
            DB::statement("UPDATE staff SET employment_type = 'permanent' WHERE employment_type IN ('part_time', 'consultant')");

            DB::statement("ALTER TABLE staff MODIFY COLUMN employment_type ENUM(
                'permanent',
                'contract',
                'casual',
                'intern'
            ) NOT NULL DEFAULT 'permanent'");
        }
    }
};
