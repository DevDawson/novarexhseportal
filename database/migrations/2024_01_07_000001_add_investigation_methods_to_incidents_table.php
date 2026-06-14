<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {

            // Which investigation method was selected for this incident.
            // Auto-suggested from risk_score per NOVAREX workflow but
            // overridable by the HSE Officer.
            $table->enum('investigation_method', [
                'five_whys',
                'fishbone',
                'taproot',
                'barrier_analysis',
            ])->nullable()->after('status');

            // Investigation workflow status (separate from incident status).
            $table->enum('investigation_status', [
                'not_started',
                'in_progress',
                'completed',
            ])->default('not_started')->after('investigation_method');

            // Responsible person and target close date for corrective actions.
            $table->string('investigation_responsible_person')->nullable()->after('investigation_status');
            $table->date('investigation_target_date')->nullable()->after('investigation_responsible_person');

            // -------------------------------------------------------
            // Approach 1: 5 WHYS
            // -------------------------------------------------------
            $table->text('why_1')->nullable();
            $table->text('why_2')->nullable();
            $table->text('why_3')->nullable();
            $table->text('why_4')->nullable();
            $table->text('why_5')->nullable();

            // -------------------------------------------------------
            // Approach 2: FISHBONE (Ishikawa)
            // Stored as JSON: { "people": ["cause1", "cause2"], "equipment": [...], ... }
            // -------------------------------------------------------
            $table->json('fishbone_data')->nullable();

            // -------------------------------------------------------
            // Approach 3: TapRooT Style
            // -------------------------------------------------------
            $table->json('taproot_timeline')->nullable();   // [{time, event}, ...]
            $table->text('taproot_witnesses')->nullable();
            $table->text('taproot_direct_causes')->nullable();
            $table->text('taproot_contributing_factors')->nullable();
            $table->text('taproot_verification_review')->nullable();

            // -------------------------------------------------------
            // Approach 4: BARRIER ANALYSIS
            // Stored as JSON: [{hazard, existing_control, control_failure, corrective_action}, ...]
            // -------------------------------------------------------
            $table->json('barrier_analysis_data')->nullable();

            // -------------------------------------------------------
            // Structured Corrective Actions (applies to all methods)
            // [{description, responsible, due_date, budget, status}, ...]
            // -------------------------------------------------------
            $table->json('corrective_actions_plan')->nullable();

            // Evidence files (comma-separated paths or JSON array of paths).
            $table->json('evidence_files')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn([
                'investigation_method', 'investigation_status',
                'investigation_responsible_person', 'investigation_target_date',
                'why_1', 'why_2', 'why_3', 'why_4', 'why_5',
                'fishbone_data', 'taproot_timeline', 'taproot_witnesses',
                'taproot_direct_causes', 'taproot_contributing_factors',
                'taproot_verification_review', 'barrier_analysis_data',
                'corrective_actions_plan', 'evidence_files',
            ]);
        });
    }
};
