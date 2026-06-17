<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_non_conformities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('internal_audit_id')
                ->constrained('internal_audits')
                ->cascadeOnDelete();

            $table->foreignId('checklist_item_id')
                ->nullable()
                ->constrained('audit_checklist_items')
                ->nullOnDelete();

            $table->string('nc_number', 20);

            $table->enum('nc_type', ['major', 'minor', 'critical']);

            $table->string('clause_reference', 50)->nullable();

            $table->text('description');
            $table->text('objective_evidence')->nullable();
            $table->string('department_responsible', 100)->nullable();

            // Risk evaluation (L × S)
            $table->tinyInteger('likelihood')->unsigned()->nullable();
            $table->tinyInteger('severity')->unsigned()->nullable();
            $table->tinyInteger('risk_score')->unsigned()->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high'])->nullable();

            // Root Cause Analysis (embedded)
            $table->enum('rca_method', ['five_whys', 'fishbone', 'both', 'none'])->default('none');
            $table->text('why_1')->nullable();
            $table->text('why_2')->nullable();
            $table->text('why_3')->nullable();
            $table->text('why_4')->nullable();
            $table->text('why_5')->nullable();
            $table->text('root_cause_summary')->nullable();
            $table->text('fishbone_people')->nullable();
            $table->text('fishbone_process')->nullable();
            $table->text('fishbone_equipment')->nullable();
            $table->text('fishbone_material')->nullable();
            $table->text('fishbone_environment')->nullable();
            $table->text('fishbone_management')->nullable();

            // Action proposals
            $table->text('corrective_action_proposed')->nullable();
            $table->text('preventive_action_proposed')->nullable();

            // Status & workflow
            $table->enum('status', ['open', 'in_progress', 'closed', 'rejected'])->default('open');

            $table->foreignId('raised_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_to_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('due_date')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->foreignId('closed_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('verified_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();
            $table->boolean('effectiveness_verified')->default(false);
            $table->text('effectiveness_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_non_conformities');
    }
};
