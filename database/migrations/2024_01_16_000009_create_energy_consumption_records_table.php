<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_consumption_records', function (Blueprint $table) {
            $table->id();
            $table->enum('energy_source', ['electricity', 'diesel', 'petrol', 'natural_gas', 'lpg', 'solar', 'biomass', 'other'])->default('electricity');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('quantity', 12, 3);
            $table->enum('unit', ['kWh', 'MWh', 'litres', 'm3', 'GJ', 'tonnes'])->default('kWh');
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('currency', 10)->default('TZS');
            $table->string('meter_reading_start')->nullable();
            $table->string('meter_reading_end')->nullable();
            $table->string('facility')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('recorded_by_id')->constrained('users');
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_consumption_records');
    }
};
