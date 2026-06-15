<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_register', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->string('activity_task');
            $table->string('location')->nullable();
            $table->text('hazard_description');

            $table->enum('hazard_category', [
                'physical',
                'chemical',
                'biological',
                'ergonomic',
                'psychosocial',
                'environmental',
                'mechanical',
                'electrical',
            ]);

            $table->text('who_might_be_harmed')->nullable();

            // Initial risk (before controls)
            $table->tinyInteger('initial_likelihood')->default(0);
            $table->tinyInteger('initial_severity')->default(0);
            $table->tinyInteger('initial_risk_score')->default(0);

            $table->text('existing_controls')->nullable();

            // Hierarchy of controls (JSON array of selected values)
            $table->json('additional_controls')->nullable();
            $table->text('additional_controls_description')->nullable();

            // Residual risk (after additional controls)
            $table->tinyInteger('residual_likelihood')->default(0);
            $table->tinyInteger('residual_severity')->default(0);
            $table->tinyInteger('residual_risk_score')->default(0);

            $table->foreignId('responsible_person_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('review_date')->nullable();

            $table->enum('status', ['open', 'controls_in_progress', 'controlled', 'closed'])
                ->default('open');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_register');
    }
};
