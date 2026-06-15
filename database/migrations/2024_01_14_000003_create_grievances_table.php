<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grievances', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();
            $table->string('complainant_name')->nullable();
            $table->string('complainant_contact')->nullable();
            $table->boolean('is_anonymous')->default(false);

            $table->enum('category', [
                'environmental',
                'social',
                'labour',
                'safety',
                'land_access',
                'noise_dust',
                'other',
            ]);

            $table->date('received_date');
            $table->text('description');

            $table->enum('status', [
                'open',
                'under_review',
                'action_taken',
                'resolved',
                'closed',
            ])->default('open');

            $table->enum('severity', [
                'low',
                'medium',
                'high',
            ])->default('medium');

            $table->text('investigation_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->date('target_resolution_date')->nullable();
            $table->date('actual_resolution_date')->nullable();
            $table->boolean('complainant_satisfied')->nullable();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('stakeholder_id')
                ->nullable()
                ->constrained('stakeholders')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grievances');
    }
};
