<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_impact_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            // Impact matrix columns
            $table->string('activity', 255)->comment('Project activity causing the impact');
            $table->string('receptor', 255)->comment('Environmental or social receptor affected');
            $table->enum('impact_category', [
                'air_quality', 'water_resources', 'soil_land', 'biodiversity',
                'noise_vibration', 'waste', 'climate', 'cultural_heritage',
                'socioeconomic', 'health_safety', 'resettlement', 'other',
            ])->default('other');
            $table->string('impact_description', 500);
            $table->enum('phase', ['pre_construction', 'construction', 'operation', 'decommissioning', 'all'])
                ->default('construction');
            $table->enum('nature', ['positive', 'negative', 'neutral'])->default('negative');
            // 4-factor significance: Severity × Likelihood × Duration × Sensitivity (1-5 each, max 625)
            $table->unsignedTinyInteger('severity')->default(1)->comment('1=Negligible 5=Catastrophic');
            $table->unsignedTinyInteger('likelihood')->default(1)->comment('1=Rare 5=Almost certain');
            $table->unsignedTinyInteger('duration')->default(1)->comment('1=<1yr 5=Permanent');
            $table->unsignedTinyInteger('sensitivity')->default(1)->comment('1=Low 5=High');
            $table->unsignedSmallInteger('significance_score')->default(1)
                ->comment('Severity × Likelihood × Duration × Sensitivity');
            $table->enum('significance_level', ['negligible', 'minor', 'moderate', 'major', 'critical'])
                ->default('minor');
            // Mitigation
            $table->text('proposed_mitigation')->nullable();
            $table->unsignedTinyInteger('residual_severity')->nullable();
            $table->unsignedTinyInteger('residual_likelihood')->nullable();
            $table->unsignedTinyInteger('residual_duration')->nullable();
            $table->unsignedTinyInteger('residual_sensitivity')->nullable();
            $table->unsignedSmallInteger('residual_significance_score')->nullable();
            $table->enum('residual_significance_level', ['negligible', 'minor', 'moderate', 'major', 'critical'])
                ->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_impact_assessments');
    }
};
