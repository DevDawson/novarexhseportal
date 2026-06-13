<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('payroll_period'); // first day of the payroll month

            // Earnings
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2)->default(0);

            // Tanzania Statutory Deductions
            $table->decimal('paye', 15, 2)->default(0);            // Pay As You Earn (TRA)
            $table->decimal('nssf', 15, 2)->default(0);            // Employee NSSF contribution
            $table->decimal('nssf_employer', 15, 2)->default(0);   // Employer NSSF contribution
            $table->decimal('wcf', 15, 2)->default(0);             // Workers Compensation Fund (employer)
            $table->decimal('nhif', 15, 2)->default(0);            // NHIF / health insurance contribution

            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);

            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_reference')->nullable();

            $table->timestamps();

            $table->unique(['staff_id', 'payroll_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
