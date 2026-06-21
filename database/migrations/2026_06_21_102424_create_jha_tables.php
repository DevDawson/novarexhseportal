<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── JHA Analysis (header record) ──────────────────────────────
        Schema::create('jha_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('jha_number')->unique();
            $table->string('title');
            $table->string('location');
            $table->date('work_date');
            $table->text('work_description');
            $table->string('department')->nullable();

            $table->unsignedSmallInteger('total_workers')->default(0);
            $table->unsignedSmallInteger('qualified_workers')->default(0);

            // KPI snapshots (recalculated on save)
            $table->decimal('competency_compliance_pct', 5, 2)->nullable();
            $table->decimal('implementation_compliance_pct', 5, 2)->nullable();
            $table->decimal('residual_risk_reduction_pct', 5, 2)->nullable();

            // Approval workflow
            $table->enum('status', [
                'draft', 'submitted', 'supervisor_approved', 'hse_approved',
                'pm_approved', 'client_approved', 'authorized', 'rejected',
            ])->default('draft');

            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->foreignId('hse_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hse_approved_at')->nullable();
            $table->foreignId('pm_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('pm_approved_at')->nullable();
            $table->foreignId('client_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('client_approved_at')->nullable();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('authorized_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Job steps / tasks ─────────────────────────────────────────
        Schema::create('jha_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jha_analysis_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('step_number');
            $table->string('task_description');
            $table->timestamps();
        });

        // ── Hazards per task ─────────────────────────────────────────
        Schema::create('jha_hazards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jha_task_id')->constrained()->cascadeOnDelete();
            $table->string('hazard_type');
            $table->string('hazard_description');

            // Initial risk: L × S × E (1–5 each, max 125)
            $table->unsignedTinyInteger('initial_likelihood')->default(1);
            $table->unsignedTinyInteger('initial_severity')->default(1);
            $table->unsignedTinyInteger('initial_exposure')->default(1);
            $table->unsignedSmallInteger('initial_risk_score')->default(1);
            $table->string('initial_risk_level')->default('low');

            // Residual risk (after controls)
            $table->unsignedTinyInteger('residual_likelihood')->default(1);
            $table->unsignedTinyInteger('residual_severity')->default(1);
            $table->unsignedTinyInteger('residual_exposure')->default(1);
            $table->unsignedSmallInteger('residual_risk_score')->default(1);
            $table->string('residual_risk_level')->default('low');
            $table->boolean('residual_accepted')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Hierarchy of Controls per hazard ─────────────────────────
        Schema::create('jha_control_measures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jha_hazard_id')->constrained()->cascadeOnDelete();
            // 1=Elimination, 2=Substitution, 3=Engineering, 4=Administrative, 5=PPE
            $table->unsignedTinyInteger('hierarchy_level');
            $table->string('description');
            $table->string('responsible_person')->nullable();
            $table->enum('status', ['planned', 'implemented', 'verified'])->default('planned');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Environmental / ESG screening per task ────────────────────
        Schema::create('jha_environment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jha_task_id')->constrained()->cascadeOnDelete();
            $table->boolean('waste_generated')->default(false);
            $table->text('waste_description')->nullable();
            $table->boolean('air_emissions')->default(false);
            $table->text('air_description')->nullable();
            $table->boolean('water_discharge')->default(false);
            $table->text('water_description')->nullable();
            $table->boolean('energy_consumption')->default(false);
            $table->text('energy_description')->nullable();
            $table->boolean('biodiversity_impact')->default(false);
            $table->text('biodiversity_description')->nullable();
            $table->boolean('community_impact')->default(false);
            $table->text('community_description')->nullable();
            $table->unsignedTinyInteger('env_likelihood')->default(1);
            $table->unsignedTinyInteger('env_consequence')->default(1);
            $table->unsignedSmallInteger('env_risk_score')->default(1);
            $table->string('env_risk_level')->default('low');
            $table->timestamps();
        });

        // ── Legal requirements per JHA ────────────────────────────────
        Schema::create('jha_legal_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jha_analysis_id')->constrained()->cascadeOnDelete();
            $table->string('legislation');
            $table->text('requirement_detail')->nullable();
            $table->boolean('compliant')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Competency requirements per JHA ───────────────────────────
        Schema::create('jha_competency_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jha_analysis_id')->constrained()->cascadeOnDelete();
            $table->string('competency_type');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('required_workers')->default(1);
            $table->unsignedSmallInteger('qualified_workers')->default(0);
            $table->decimal('compliance_pct', 5, 2)->default(0);
            $table->timestamps();
        });

        // ── Field monitoring / verification checklist ─────────────────
        Schema::create('jha_monitoring_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jha_analysis_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('checked_at');
            $table->boolean('controls_implemented')->default(false);
            $table->boolean('ppe_available')->default(false);
            $table->boolean('permit_active')->default(false);
            $table->boolean('workers_briefed')->default(false);
            $table->boolean('emergency_equipment_available')->default(false);
            $table->decimal('compliance_pct', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jha_monitoring_checks');
        Schema::dropIfExists('jha_competency_requirements');
        Schema::dropIfExists('jha_legal_requirements');
        Schema::dropIfExists('jha_environment');
        Schema::dropIfExists('jha_control_measures');
        Schema::dropIfExists('jha_hazards');
        Schema::dropIfExists('jha_tasks');
        Schema::dropIfExists('jha_analyses');
    }
};
