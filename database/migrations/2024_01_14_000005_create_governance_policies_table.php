<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governance_policies', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('policy_number')->nullable();

            $table->enum('policy_type', [
                'hse',
                'esg',
                'hr',
                'finance',
                'ethics',
                'data_privacy',
                'procurement',
                'other',
            ]);

            $table->string('document_owner');  // name or department
            $table->date('effective_date');
            $table->date('review_date');
            $table->date('last_reviewed_date')->nullable();

            $table->enum('status', [
                'draft',
                'active',
                'under_review',
                'superseded',
                'archived',
            ])->default('draft');

            $table->string('version', 20)->default('1.0');
            $table->text('scope')->nullable();
            $table->string('document_file')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('governance_policies');
    }
};
