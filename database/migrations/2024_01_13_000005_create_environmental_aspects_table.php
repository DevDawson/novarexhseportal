<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environmental_aspects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->string('activity_process');
            $table->string('environmental_aspect');
            $table->text('environmental_impact');

            $table->enum('impact_category', [
                'air', 'water', 'soil', 'waste',
                'biodiversity', 'noise', 'energy', 'other',
            ]);

            $table->tinyInteger('likelihood')->default(0);
            $table->tinyInteger('severity')->default(0);
            $table->tinyInteger('significance_score')->default(0);

            $table->text('existing_controls')->nullable();
            $table->string('legal_requirement_ref')->nullable();

            $table->foreignId('responsible_person_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('review_date')->nullable();

            $table->enum('status', ['significant', 'not_significant', 'controlled'])
                ->default('not_significant');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_aspects');
    }
};
