<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environmental_monitoring_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->date('record_date');

            $table->enum('metric_type', [
                'water_consumption',
                'energy_consumption',
                'fuel_consumption',
                'waste_generated_hazardous',
                'waste_generated_nonhazardous',
                'waste_recycled',
                'ghg_emissions',
                'spills_incidents',
            ]);

            $table->decimal('value', 12, 2);
            $table->string('unit', 30);
            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Prevent duplicate entries for the same project/date/metric
            $table->unique(['project_id', 'record_date', 'metric_type'], 'emr_project_date_metric_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_monitoring_records');
    }
};
