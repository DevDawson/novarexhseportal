<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expands incident_investigations to support the full 12-section
     * TapRooT Style Investigation structure:
     *
     *   1. Incident Overview          -> covered by Incident model
     *                                     (description/location/date/reported_by)
     *                                     + people_involved (new)
     *   2. Event Timeline              -> timeline_events JSON (existing),
     *                                     each entry now carries a 'phase'
     *                                     (before/during/after)
     *   3. Task / Activity Description -> task_description,
     *                                     procedures_followed,
     *                                     deviations_from_practice (new)
     *   4. Direct Causes                -> direct_causes (existing) +
     *                                     unsafe_acts_conditions,
     *                                     immediate_failures (new)
     *   5. Contributing Causes          -> contributing_factors (existing) +
     *                                     equipment_environmental_human_factors,
     *                                     communication_supervision_gaps (new)
     *   6. Root Causes (System Failures)-> root_cause (existing) +
     *                                     training_supervision_failure,
     *                                     risk_assessment_adequacy,
     *                                     maintenance_inspection_effectiveness (new)
     *   7. Safeguards / Barriers        -> investigation_barrier_items table (existing)
     *   8. Human Performance Factors    -> task_understanding,
     *                                     distractions_fatigue_stress,
     *                                     competency_assessment (new)
     *   9. Corrective Actions           -> recommendations (existing,
     *                                     reused as "Corrective Actions")
     *  10. Preventive Actions           -> preventive_actions (new)
     *  11. Effectiveness Verification   -> verification_notes/date (existing) +
     *                                     effectiveness_indicators (new)
     *  12. Management Review            -> lessons_learned,
     *                                     management_review_notes (new)
     */
    public function up(): void
    {
        Schema::table('incident_investigations', function (Blueprint $table) {
            // --- Section 1: Incident Overview (additional) ---------------
            $table->text('people_involved')->nullable()->after('incident_id');

            // --- Section 3: Task / Activity Description ------------------
            $table->text('task_description')->nullable()->after('timeline_events');
            $table->text('procedures_followed')->nullable()->after('task_description');
            $table->text('deviations_from_practice')->nullable()->after('procedures_followed');

            // --- Section 4: Direct Causes (additional) --------------------
            $table->text('unsafe_acts_conditions')->nullable()->after('direct_causes');
            $table->text('immediate_failures')->nullable()->after('unsafe_acts_conditions');

            // --- Section 5: Contributing Causes (additional) --------------
            $table->text('equipment_environmental_human_factors')->nullable()->after('contributing_factors');
            $table->text('communication_supervision_gaps')->nullable()->after('equipment_environmental_human_factors');

            // --- Section 6: Root Causes (System Failures) ------------------
            $table->text('training_supervision_failure')->nullable()->after('root_cause');
            $table->text('risk_assessment_adequacy')->nullable()->after('training_supervision_failure');
            $table->text('maintenance_inspection_effectiveness')->nullable()->after('risk_assessment_adequacy');

            // --- Section 8: Human Performance Factors -----------------------
            $table->text('task_understanding')->nullable()->after('maintenance_inspection_effectiveness');
            $table->text('distractions_fatigue_stress')->nullable()->after('task_understanding');
            $table->text('competency_assessment')->nullable()->after('distractions_fatigue_stress');

            // --- Section 10: Preventive Actions ------------------------------
            // (Section 9, Corrective Actions, reuses existing 'recommendations')
            $table->text('preventive_actions')->nullable()->after('recommendations');

            // --- Section 11: Effectiveness Verification (additional) --------
            $table->text('effectiveness_indicators')->nullable()->after('verification_notes');

            // --- Section 12: Management Review --------------------------------
            $table->text('lessons_learned')->nullable()->after('verification_date');
            $table->text('management_review_notes')->nullable()->after('lessons_learned');
        });
    }

    public function down(): void
    {
        Schema::table('incident_investigations', function (Blueprint $table) {
            $table->dropColumn([
                'people_involved',
                'task_description',
                'procedures_followed',
                'deviations_from_practice',
                'unsafe_acts_conditions',
                'immediate_failures',
                'equipment_environmental_human_factors',
                'communication_supervision_gaps',
                'training_supervision_failure',
                'risk_assessment_adequacy',
                'maintenance_inspection_effectiveness',
                'task_understanding',
                'distractions_fatigue_stress',
                'competency_assessment',
                'preventive_actions',
                'effectiveness_indicators',
                'lessons_learned',
                'management_review_notes',
            ]);
        });
    }
};
