<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ethics_incidents', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();

            $table->enum('incident_type', [
                'bribery_corruption',
                'fraud',
                'conflict_of_interest',
                'discrimination',
                'harassment',
                'data_breach',
                'misconduct',
                'whistleblower',
                'other',
            ]);

            $table->date('reported_date');
            $table->boolean('is_anonymous')->default(false);
            $table->text('description');

            $table->enum('severity', [
                'low',
                'medium',
                'high',
                'critical',
            ])->default('medium');

            $table->enum('status', [
                'reported',
                'under_investigation',
                'action_taken',
                'closed',
                'no_action_required',
            ])->default('reported');

            $table->text('investigation_findings')->nullable();
            $table->text('corrective_action')->nullable();
            $table->date('closure_date')->nullable();

            $table->foreignId('investigated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ethics_incidents');
    }
};
