<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── EMS Continual Improvement Actions (ISO 14001 Clause 10) ──
        Schema::create('ems_improvement_actions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();  // e.g. EMS-CI-2026-0001

            // Source of the improvement opportunity
            $table->enum('source', [
                'management_review',
                'internal_audit',
                'compliance_evaluation',
                'kpi_analysis',
                'incident',
                'corrective_action',
                'employee_suggestion',
                'external_audit',
                'stakeholder_feedback',
                'other',
            ])->default('kpi_analysis');

            // PDCA context
            $table->enum('pdca_phase', ['plan', 'do', 'check', 'act'])->default('act');

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('expected_benefit')->nullable();

            // Linkages
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('raised_by_id')->nullable()->constrained('users')->nullOnDelete();

            // Tracking
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'completed', 'verified', 'closed', 'cancelled'])
                  ->default('open');
            $table->date('target_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('action_taken')->nullable();

            // Effectiveness
            $table->boolean('effectiveness_verified')->default(false);
            $table->text('effectiveness_notes')->nullable();
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('verified_date')->nullable();

            // KPI linkage — which EMS KPI this improvement targets
            $table->string('target_kpi')->nullable(); // e.g. 'compliance_rate', 'audit_score'

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ems_improvement_actions');
    }
};
