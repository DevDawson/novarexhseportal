<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('expense_date');
            $table->enum('category', ['transport', 'accommodation', 'meals', 'fuel', 'supplies', 'communication', 'other'])->default('other');
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('receipt_file')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'reimbursed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_expenses');
    }
};
