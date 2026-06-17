<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_alternatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('screening_id')->nullable()->constrained('esia_screenings')->nullOnDelete();

            $table->enum('alternative_type', [
                'no_project',     // No-project option (baseline)
                'site',           // Alternative site/location
                'technology',     // Alternative technology/equipment
                'design',         // Alternative project design
                'process',        // Alternative operational process
                'energy_source',  // Alternative energy source
                'other',
            ])->default('technology');

            $table->string('title', 255);
            $table->text('description');

            // Evaluation criteria (1-5 each)
            // For environmental_impact & cost: 1=best (lowest), 5=worst (highest)
            // For social_acceptance & feasibility: 5=best (highest), 1=worst (lowest)
            $table->unsignedTinyInteger('environmental_impact')->default(3)
                ->comment('1=Negligible impact, 5=Severe impact — LOWER IS BETTER');
            $table->unsignedTinyInteger('cost_factor')->default(3)
                ->comment('1=Very low cost, 5=Very high cost — LOWER IS BETTER');
            $table->unsignedTinyInteger('social_acceptance')->default(3)
                ->comment('1=Very low acceptance, 5=High acceptance — HIGHER IS BETTER');
            $table->unsignedTinyInteger('feasibility')->default(3)
                ->comment('1=Not feasible, 5=Highly feasible — HIGHER IS BETTER');

            // Composite preference score = (social_acceptance + feasibility) + (6-environmental_impact) + (6-cost_factor)
            // Range: 4-20; higher = more preferred alternative
            $table->unsignedTinyInteger('preference_score')->default(12)
                ->comment('Computed: (SA + F) + (6-EI) + (6-CF); higher = better');

            $table->boolean('is_recommended')->default(false);
            $table->text('recommendation_notes')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('evaluated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_alternatives');
    }
};
