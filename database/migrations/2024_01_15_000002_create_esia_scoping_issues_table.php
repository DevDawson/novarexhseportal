<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_scoping_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('screening_id')->nullable()->constrained('esia_screenings')->nullOnDelete();
            $table->enum('issue_type', [
                'air_quality', 'water_resources', 'soil_land', 'biodiversity',
                'noise_vibration', 'waste_management', 'climate', 'cultural_heritage',
                'socioeconomic', 'health_safety', 'resettlement', 'gender_inclusion', 'other',
            ])->default('other');
            $table->string('issue_title')->maxlength(255);
            $table->text('description');
            $table->text('data_required')->nullable();
            $table->text('methodology')->nullable();
            $table->string('responsible_expert')->nullable()->maxlength(255);
            $table->boolean('included_in_scope')->default(true);
            $table->text('exclusion_justification')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_scoping_issues');
    }
};
