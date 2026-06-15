<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_baseline_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('parameter_type', [
                'air_quality', 'water_quality', 'soil_quality', 'noise_level',
                'biodiversity', 'socioeconomic', 'health', 'land_use', 'other',
            ])->default('other');
            $table->string('parameter_name')->maxlength(255);
            $table->string('sampling_location')->nullable()->maxlength(255);
            $table->decimal('measurement_value', 14, 4)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('standard_limit', 100)->nullable()->comment('Applicable regulatory limit');
            $table->boolean('exceeds_limit')->default(false);
            $table->date('measurement_date')->nullable();
            $table->string('data_source', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_baseline_data');
    }
};
