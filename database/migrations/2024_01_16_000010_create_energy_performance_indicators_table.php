<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_performance_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('indicator_name');
            $table->text('description')->nullable();
            $table->text('formula')->nullable();
            $table->string('unit_of_measure');
            $table->enum('energy_source', ['electricity', 'diesel', 'petrol', 'natural_gas', 'lpg', 'solar', 'all'])->default('all');
            $table->decimal('baseline_value', 12, 4)->nullable();
            $table->decimal('target_value', 12, 4)->nullable();
            $table->decimal('current_value', 12, 4)->nullable();
            $table->enum('period', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->foreignId('responsible_id')->constrained('users');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_performance_indicators');
    }
};
