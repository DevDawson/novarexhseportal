<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_capa_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nc_id')
                ->constrained('audit_non_conformities')
                ->cascadeOnDelete();

            $table->foreignId('internal_audit_id')
                ->constrained('internal_audits')
                ->cascadeOnDelete();

            $table->string('action_number', 20);

            $table->enum('action_type', ['corrective', 'preventive'])->default('corrective');

            $table->text('description');
            $table->text('root_cause_addressed')->nullable();

            $table->foreignId('responsible_person_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('department', 100)->nullable();

            $table->date('target_date');
            $table->date('actual_completion_date')->nullable();

            $table->enum('status', ['open', 'in_progress', 'completed', 'verified'])
                ->default('open');

            $table->text('evidence_notes')->nullable();
            $table->string('evidence_file')->nullable();

            $table->enum('verification_status', ['pending', 'passed', 'failed', 'not_due'])
                ->default('pending');

            $table->foreignId('verified_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();
            $table->boolean('effectiveness_check')->default(false);
            $table->text('effectiveness_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_capa_actions');
    }
};
