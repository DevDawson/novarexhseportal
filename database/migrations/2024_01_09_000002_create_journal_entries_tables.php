<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->date('entry_date');
            $table->string('description');

            // 'manual' for entries created directly by an Accountant,
            // or a system source type for automatic postings, e.g.
            // 'payroll_approval', 'payroll_payment', 'statutory_remittance'.
            $table->string('source_type')->default('manual');

            // ID of the record that triggered an automatic posting
            // (e.g. payroll.id for 'payroll_approval'/'payroll_payment').
            // Null for manual entries.
            $table->unsignedBigInteger('source_id')->nullable();

            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts');

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
