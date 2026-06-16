<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_baselines', function (Blueprint $table) {
            $table->id();
            $table->enum('energy_source', ['electricity', 'diesel', 'petrol', 'natural_gas', 'lpg', 'solar', 'biomass', 'total'])->default('electricity');
            $table->date('baseline_period_start');
            $table->date('baseline_period_end');
            $table->decimal('total_consumption', 12, 3);
            $table->enum('unit', ['kWh', 'MWh', 'litres', 'm3', 'GJ', 'tonnes'])->default('kWh');
            $table->text('methodology')->nullable();
            $table->text('adjustment_factors')->nullable();
            $table->foreignId('established_by_id')->constrained('users');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approved_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_baselines');
    }
};
