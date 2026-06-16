<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazop_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazop_study_id')->constrained('hazop_studies')->cascadeOnDelete();

            // Node identification
            $table->unsignedSmallInteger('node_number')->nullable();
            $table->string('node_name')->nullable()->comment('Process area or node name');
            $table->string('parameter')->nullable()->comment('Flow, Pressure, Temperature, etc.');
            $table->string('guide_word')->nullable()->comment('NO, MORE, LESS, REVERSE, etc.');
            $table->text('deviation')->nullable()->comment('What happens when guide word is applied to the parameter');

            // Cause & consequence analysis
            $table->text('cause')->nullable();
            $table->text('consequence')->nullable();
            $table->text('existing_safeguards')->nullable();

            // Initial risk assessment (L×S×E)
            $table->unsignedTinyInteger('likelihood')->default(1);
            $table->unsignedTinyInteger('severity')->default(1);
            $table->unsignedTinyInteger('exposure')->default(1);
            $table->unsignedSmallInteger('initial_risk_score')->default(0);
            $table->enum('risk_classification', ['low', 'medium', 'high', 'critical'])->default('low');

            // Risk Priority Number (S×O×D — HAZOP/FMEA method)
            $table->unsignedTinyInteger('rpn_severity')->default(1);
            $table->unsignedTinyInteger('rpn_occurrence')->default(1);
            $table->unsignedTinyInteger('rpn_detectability')->default(1);
            $table->unsignedSmallInteger('rpn_score')->default(0);

            // Controls & residual risk
            $table->text('recommended_actions')->nullable();
            $table->decimal('control_effectiveness', 5, 2)->default(0)->comment('0–100 percent');
            $table->decimal('residual_risk_score', 8, 2)->default(0)->comment('IR × (1 − CE%)');
            $table->decimal('risk_reduction_factor', 8, 2)->default(0)->comment('IR / RR');
            $table->enum('residual_risk_classification', ['low', 'medium', 'high', 'critical'])->default('low');

            // Action assignment
            $table->foreignId('risk_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->date('due_date')->nullable();

            // Approval
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approval_date')->nullable();

            // Closure verification
            $table->text('closure_verification')->nullable();
            $table->foreignId('closure_verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('closure_date')->nullable();

            $table->enum('status', [
                'open',
                'action_assigned',
                'in_progress',
                'verification_pending',
                'closed',
            ])->default('open');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazop_nodes');
    }
};
