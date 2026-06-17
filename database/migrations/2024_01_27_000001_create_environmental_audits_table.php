<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environmental_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_number', 30)->unique();
            $table->string('audit_title');
            $table->enum('audit_type', ['internal', 'external', 'compliance', 'supplier', 'regulatory'])->default('internal');
            $table->string('audit_reference', 50)->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'closed'])->default('planned');

            // Scope & Objectives
            $table->text('scope')->nullable();
            $table->text('objectives')->nullable();
            $table->text('criteria')->nullable();

            // Location & Planning
            $table->string('site_location', 255)->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->date('audit_date')->nullable();
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->unsignedSmallInteger('audit_duration_days')->default(1);
            $table->enum('audit_method', ['on_site', 'remote', 'hybrid'])->default('on_site');

            // Audit Team
            $table->foreignId('team_leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lead_auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('co_auditors')->nullable();
            $table->text('technical_experts')->nullable();
            $table->text('auditee_representatives')->nullable();

            // Scoring (auto-computed)
            $table->decimal('compliance_score', 5, 2)->default(0);
            $table->enum('rating', ['excellent', 'good', 'fair', 'poor'])->nullable();

            // Outputs
            $table->text('management_summary')->nullable();
            $table->text('closing_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_audits');
    }
};
