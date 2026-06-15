<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_regulatory_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('report_id')->nullable()->constrained('esia_reports')->nullOnDelete();
            $table->string('regulatory_authority', 255)->default('NEMC')
                ->comment('e.g. NEMC, OSHA, VPO-DoE');
            $table->enum('submission_type', ['screening', 'scoping', 'draft_eia', 'final_eia', 'esmp', 'compliance_report'])
                ->default('draft_eia');
            $table->string('reference_number', 100)->nullable();
            $table->date('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'additional_info_required', 'approved', 'rejected'])
                ->default('draft');
            $table->text('submission_notes')->nullable();
            $table->text('review_comments')->nullable();
            $table->date('decision_date')->nullable();
            $table->text('approval_conditions')->nullable();
            $table->date('approval_expiry_date')->nullable();
            $table->string('certificate_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_regulatory_submissions');
    }
};
