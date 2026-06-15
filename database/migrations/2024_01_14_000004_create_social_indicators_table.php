<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_indicators', function (Blueprint $table) {
            $table->id();

            $table->enum('indicator_type', [
                'total_employees',
                'female_employees',
                'local_employees',
                'employees_trained',
                'training_hours',
                'community_investment_amount',
                'community_beneficiaries',
                'local_procurement_percent',
                'contractor_workforce',
                'new_hires',
                'employee_turnover',
                'lost_workdays',
            ]);

            $table->string('period');           // e.g. "2026-Q1", "2026-06"
            $table->decimal('value', 14, 2);
            $table->string('unit', 30)->nullable(); // e.g. "persons", "%", "USD", "hours"
            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['indicator_type', 'period'], 'social_indicators_type_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_indicators');
    }
};
