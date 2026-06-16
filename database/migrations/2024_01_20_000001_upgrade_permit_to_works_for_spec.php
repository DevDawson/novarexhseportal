<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permit_to_works', function (Blueprint $table) {
            // -- Work order integration ----------------------------------------
            $table->string('work_order_id', 100)->nullable()->after('permit_number');

            // -- Additional location & context --------------------------------
            $table->string('site_area', 255)->nullable()->after('location');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('project_id');

            // -- Contractor & workforce ----------------------------------------
            $table->string('contractor_company', 255)->nullable()->after('area_authority_id');
            $table->string('contractor_name', 255)->nullable()->after('contractor_company');
            $table->unsignedSmallInteger('number_of_workers')->default(1)->after('contractor_name');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete()->after('number_of_workers');
            $table->string('duration_estimate', 100)->nullable()->after('supervisor_id');
            $table->dateTime('actual_start')->nullable()->after('duration_estimate');
            $table->dateTime('actual_completion')->nullable()->after('actual_start');

            // -- Risk assessment (L×S) ----------------------------------------
            $table->unsignedTinyInteger('likelihood')->default(1)->after('actual_completion');
            $table->unsignedTinyInteger('severity')->default(1)->after('likelihood');
            $table->unsignedTinyInteger('risk_score')->default(0)->after('severity');
            $table->enum('risk_classification', ['low', 'medium', 'high'])->default('low')->after('risk_score');
            $table->foreignId('linked_hazard_id')->nullable()->constrained('hazard_register')->nullOnDelete()->after('risk_classification');
            $table->foreignId('linked_hazop_node_id')->nullable()->constrained('hazop_nodes')->nullOnDelete()->after('linked_hazard_id');

            // -- Additional safety control flags ------------------------------
            $table->boolean('loto_verified')->default(false)->after('isolation_details');
            $table->boolean('gas_testing_verified')->default(false)->after('gas_test_results');
            $table->boolean('fire_watch_required')->default(false)->after('gas_testing_verified');
            $table->boolean('fire_watch_confirmed')->default(false)->after('fire_watch_required');
            $table->boolean('barricading_required')->default(false)->after('fire_watch_confirmed');
            $table->boolean('barricading_confirmed')->default(false)->after('barricading_required');
            $table->boolean('emergency_standby_required')->default(false)->after('barricading_confirmed');
            $table->boolean('emergency_standby_confirmed')->default(false)->after('emergency_standby_required');

            // -- Sequential approval tracking --------------------------------
            $table->enum('current_approval_stage', ['supervisor', 'hse_officer', 'site_manager'])->default('supervisor')->after('emergency_standby_confirmed');
            $table->dateTime('approved_at')->nullable()->after('current_approval_stage');
            $table->foreignId('final_approved_by_id')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');

            // -- Enhanced closure --------------------------------------------
            $table->foreignId('completion_confirmed_by_id')->nullable()->constrained('users')->nullOnDelete()->after('closeout_at');
            $table->dateTime('completion_date')->nullable()->after('completion_confirmed_by_id');
            $table->text('final_inspection_notes')->nullable()->after('completion_date');
            $table->foreignId('linked_incident_id')->nullable()->constrained('incidents')->nullOnDelete()->after('final_inspection_notes');
        });

        // Extend permit_type ENUM to add 5 new work types
        DB::statement("
            ALTER TABLE permit_to_works
            MODIFY COLUMN permit_type ENUM(
                'hot_work','confined_space','working_at_height','electrical_isolation',
                'excavation','lifting_operations','cold_work','general',
                'pressure_system','chemical_handling','radiation_work',
                'commissioning','general_maintenance'
            ) NOT NULL
        ");

        // Extend status ENUM to add under_review and preparation_verified
        DB::statement("
            ALTER TABLE permit_to_works
            MODIFY COLUMN status ENUM(
                'draft','submitted','under_review','preparation_verified',
                'approved','active','suspended','closed','cancelled','expired'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        Schema::table('permit_to_works', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['supervisor_id']);
            $table->dropForeign(['linked_hazard_id']);
            $table->dropForeign(['linked_hazop_node_id']);
            $table->dropForeign(['final_approved_by_id']);
            $table->dropForeign(['completion_confirmed_by_id']);
            $table->dropForeign(['linked_incident_id']);
            $table->dropColumn([
                'work_order_id','site_area','department_id','contractor_company','contractor_name',
                'number_of_workers','supervisor_id','duration_estimate','actual_start','actual_completion',
                'likelihood','severity','risk_score','risk_classification',
                'linked_hazard_id','linked_hazop_node_id','loto_verified','gas_testing_verified',
                'fire_watch_required','fire_watch_confirmed','barricading_required','barricading_confirmed',
                'emergency_standby_required','emergency_standby_confirmed',
                'current_approval_stage','approved_at','final_approved_by_id',
                'completion_confirmed_by_id','completion_date','final_inspection_notes','linked_incident_id',
            ]);
        });
    }
};
