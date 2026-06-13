<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('tender_title');
            $table->string('tender_number')->nullable();
            $table->string('procuring_entity')->nullable();
            $table->text('description')->nullable();
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->date('submission_deadline')->nullable();
            $table->enum('stage', ['identified', 'prequalification', 'proposal_preparation', 'submitted', 'shortlisted', 'won', 'lost', 'cancelled'])->default('identified');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('win_probability')->default(0); // 0-100
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
