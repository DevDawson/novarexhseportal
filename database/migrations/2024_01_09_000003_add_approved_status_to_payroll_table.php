<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Salary Approval Workflow per spec:
        //   HR generates payroll (Pending)
        //   -> Finance Manager / MD approves (Approved)
        //   -> Payment processed (Paid)
        //
        // 'Approved' triggers the first automatic journal posting
        // (DR Staff Salary Expense, CR Salary Payable + statutory payables).
        // 'Paid' triggers the second (DR Salary Payable, CR Bank).
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE payroll MODIFY COLUMN payment_status ENUM(
                'pending',
                'approved',
                'paid'
            ) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("UPDATE payroll SET payment_status = 'pending' WHERE payment_status = 'approved'");

            DB::statement("ALTER TABLE payroll MODIFY COLUMN payment_status ENUM(
                'pending',
                'paid'
            ) NOT NULL DEFAULT 'pending'");
        }
    }
};
