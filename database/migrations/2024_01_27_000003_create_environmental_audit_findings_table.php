<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environmental_audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('environmental_audits')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')
                ->nullable()
                ->constrained('environmental_audit_checklist_items')
                ->nullOnDelete();
            $table->string('finding_number', 20);
            $table->string('clause_reference', 100)->nullable();
            $table->string('process_area', 255)->nullable();
            $table->enum('finding_type', ['major_nc', 'minor_nc', 'observation', 'ofi'])->default('minor_nc');
            $table->text('description');
            $table->text('objective_evidence')->nullable();
            $table->text('root_cause_analysis')->nullable();
            $table->enum('environmental_impact_category', [
                'air', 'water', 'soil', 'noise', 'waste', 'other',
            ])->nullable();
            // Risk Evaluation
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->unsignedTinyInteger('likelihood')->default(1);
            $table->unsignedTinyInteger('severity')->default(1);
            $table->unsignedTinyInteger('risk_score')->default(1);
            $table->boolean('regulatory_impact')->default(false);
            // Corrective Action
            $table->text('recommended_action')->nullable();
            $table->string('action_owner', 255)->nullable();
            $table->string('department_responsible', 255)->nullable();
            $table->date('target_completion_date')->nullable();
            $table->enum('priority_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('action_status', ['open', 'in_progress', 'closed'])->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('effectiveness_verified')->default(false);
            $table->text('effectiveness_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_audit_findings');
    }
};
