<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_action_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('opportunity_type', ['efficiency_improvement', 'renewable_energy', 'behavioral_change', 'technology_upgrade', 'process_optimization', 'other'])->default('efficiency_improvement');
            $table->enum('energy_source_affected', ['electricity', 'diesel', 'petrol', 'natural_gas', 'lpg', 'solar', 'all'])->default('all');
            $table->decimal('expected_saving_quantity', 12, 3)->nullable();
            $table->string('expected_saving_unit')->nullable();
            $table->decimal('expected_cost', 12, 2)->nullable();
            $table->decimal('actual_saving', 12, 3)->nullable();
            $table->foreignId('assigned_to_id')->constrained('users');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_date');
            $table->date('completion_date')->nullable();
            $table->enum('status', ['proposed', 'approved', 'in_progress', 'completed', 'cancelled'])->default('proposed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_action_plans');
    }
};
