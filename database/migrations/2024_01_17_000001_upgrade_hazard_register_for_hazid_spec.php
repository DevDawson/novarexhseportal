<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hazard_register', function (Blueprint $table) {
            // Auto-generated reference (HZ-YYYY-MM-NNNN)
            $table->string('hazard_id', 30)->nullable()->unique()->after('id');

            // Assessment header
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('project_id');
            $table->date('date_identified')->nullable()->after('department_id');
            $table->foreignId('identified_by_id')->nullable()->constrained('users')->nullOnDelete()->after('date_identified');

            // Hazard detail
            $table->string('hazard_source')->nullable()->after('hazard_description');
            $table->text('potential_causes')->nullable()->after('hazard_source');
            $table->text('potential_consequences')->nullable()->after('potential_causes');
            $table->text('justification_of_risk_rating')->nullable()->after('who_might_be_harmed');

            // Action tracking
            $table->enum('priority_level', ['low', 'medium', 'high', 'critical'])->nullable()->after('responsible_person_id');
            $table->enum('escalation_level', ['supervisor', 'hse_officer', 'hse_manager', 'top_management'])->nullable()->after('priority_level');
            $table->date('target_completion_date')->nullable()->after('escalation_level');

            // Approval
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete()->after('target_completion_date');
            $table->date('approval_date')->nullable()->after('approved_by_id');

            // Verification (Step 5)
            $table->text('verification_method')->nullable()->after('approval_date');
            $table->text('verification_evidence')->nullable()->after('verification_method');
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete()->after('verification_evidence');
            $table->date('verification_date')->nullable()->after('verified_by_id');

            // Closure (Step 6)
            $table->text('closure_comments')->nullable()->after('verification_date');
            $table->foreignId('closed_by_id')->nullable()->constrained('users')->nullOnDelete()->after('closure_comments');
            $table->date('closure_date')->nullable()->after('closed_by_id');
        });

        // Expand status enum to include workflow steps (preserves existing values)
        DB::statement("ALTER TABLE hazard_register MODIFY COLUMN status ENUM(
            'draft',
            'open',
            'under_assessment',
            'action_required',
            'controls_in_progress',
            'verification_pending',
            'controlled',
            'closed'
        ) NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        Schema::table('hazard_register', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('identified_by_id');
            $table->dropConstrainedForeignId('approved_by_id');
            $table->dropConstrainedForeignId('verified_by_id');
            $table->dropConstrainedForeignId('closed_by_id');
            $table->dropColumn([
                'hazard_id', 'date_identified', 'hazard_source', 'potential_causes',
                'potential_consequences', 'justification_of_risk_rating',
                'priority_level', 'escalation_level', 'target_completion_date',
                'approval_date', 'verification_method', 'verification_evidence',
                'verification_date', 'closure_comments', 'closure_date',
            ]);
        });

        DB::statement("ALTER TABLE hazard_register MODIFY COLUMN status ENUM(
            'open', 'controls_in_progress', 'controlled', 'closed'
        ) NOT NULL DEFAULT 'open'");
    }
};
