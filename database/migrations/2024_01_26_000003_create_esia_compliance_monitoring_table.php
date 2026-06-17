<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_compliance_monitoring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('mitigation_id')->nullable()->constrained('esia_mitigation_actions')->nullOnDelete();
            $table->enum('monitoring_type', [
                'self_monitoring', 'third_party', 'regulatory_inspection', 'community_monitoring',
            ])->default('self_monitoring');
            $table->string('parameter_monitored', 255);
            $table->enum('monitoring_frequency', [
                'daily', 'weekly', 'monthly', 'quarterly', 'semi_annual', 'annual', 'event_based',
            ])->default('monthly');
            $table->date('monitoring_date');
            $table->string('monitored_by', 255)->nullable();
            $table->decimal('result_value', 14, 4)->nullable();
            $table->string('result_unit', 50)->nullable();
            $table->text('result_description')->nullable();
            $table->enum('compliance_status', [
                'compliant', 'non_compliant', 'partial', 'not_assessed',
            ])->default('not_assessed');
            $table->text('corrective_action')->nullable();
            $table->date('corrective_action_due')->nullable();
            $table->date('corrective_action_completed')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('verified_at')->nullable();
            $table->string('evidence_file', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_compliance_monitoring');
    }
};
