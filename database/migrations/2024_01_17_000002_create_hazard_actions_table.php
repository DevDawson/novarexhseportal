<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_register_id')->constrained('hazard_register')->cascadeOnDelete();
            $table->text('action_description');
            $table->foreignId('action_owner_id')->constrained('users');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->date('due_date');
            $table->enum('verification_status', ['pending', 'verified', 'failed'])->default('pending');
            $table->enum('closure_status', ['open', 'closed'])->default('open');
            $table->date('completed_date')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_actions');
    }
};
