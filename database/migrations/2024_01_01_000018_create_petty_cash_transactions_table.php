<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petty_cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type', ['top_up', 'expense', 'utility_payment'])->default('expense');
            $table->enum('category', ['office_supplies', 'electricity', 'water', 'internet', 'rent', 'transport', 'other'])->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('description')->nullable();
            $table->date('transaction_date');
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_transactions');
    }
};
