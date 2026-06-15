<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esg_targets', function (Blueprint $table) {
            $table->id();

            $table->string('indicator');           // e.g. "GHG Emissions Reduction"
            $table->enum('category', [
                'environmental',
                'social',
                'governance',
            ]);

            $table->string('period');              // e.g. "2026", "2026-Q2"
            $table->string('unit', 30);            // e.g. "%", "tCO2e", "USD", "persons"
            $table->decimal('baseline_value', 14, 2)->nullable();
            $table->decimal('target_value', 14, 2);
            $table->decimal('actual_value', 14, 2)->nullable();

            $table->enum('status', [
                'on_track',
                'at_risk',
                'off_track',
                'achieved',
                'not_started',
            ])->default('not_started');

            $table->text('notes')->nullable();

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esg_targets');
    }
};
