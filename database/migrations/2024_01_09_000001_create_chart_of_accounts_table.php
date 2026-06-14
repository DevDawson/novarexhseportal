<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'income', 'expense']);

            // The side on which increases to this account are recorded.
            // Asset & Expense accounts normally increase on the Debit side.
            // Liability, Equity & Income accounts normally increase on the Credit side.
            $table->enum('normal_balance', ['debit', 'credit']);

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)
                ->comment('System accounts are used by automatic payroll journal posting and cannot be deleted.');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
