<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            // Snapshot of the staff member's employment type at the time this
            // payroll record was created - drives which gross-pay formula applies.
            $table->enum('employment_type', [
                'permanent', 'part_time', 'casual', 'consultant', 'contract', 'intern',
            ])->default('permanent')->after('staff_id');

            // --- Variable-pay inputs (Part-Time / Casual) ---------------
            // Either entered manually or pulled from the Attendance log
            // for the payroll_period month.
            $table->decimal('hours_worked', 7, 2)->nullable()->after('allowances')
                ->comment('Part-Time: Gross Pay = Hours Worked x Hourly Rate');

            $table->decimal('days_worked', 5, 2)->nullable()->after('hours_worked')
                ->comment('Casual: Gross Pay = Days Worked x Daily Rate');

            // --- Overtime (Permanent / Part-Time / Casual) ---------------
            $table->decimal('overtime_hours', 7, 2)->default(0)->after('days_worked')
                ->comment('Sum of Attendance.overtime_hours for the period');

            $table->decimal('overtime_pay', 15, 2)->default(0)->after('overtime_hours')
                ->comment('Overtime_Pay = Overtime_Hours x Hourly_Rate x 1.5');

            // --- Additional earnings -------------------------------------
            $table->decimal('bonus', 15, 2)->default(0)->after('overtime_pay');

            // --- Additional deductions ------------------------------------
            $table->decimal('loan_deduction', 15, 2)->default(0)->after('other_deductions');
            $table->decimal('advance_deduction', 15, 2)->default(0)->after('loan_deduction');

            // --- Consultants ------------------------------------------------
            $table->decimal('withholding_tax', 15, 2)->default(0)->after('advance_deduction')
                ->comment('Consultant: Net_Payment = Contract_Amount - Withholding_Tax (manual entry)');
        });
    }

    public function down(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $table->dropColumn([
                'employment_type',
                'hours_worked',
                'days_worked',
                'overtime_hours',
                'overtime_pay',
                'bonus',
                'loan_deduction',
                'advance_deduction',
                'withholding_tax',
            ]);
        });
    }
};
