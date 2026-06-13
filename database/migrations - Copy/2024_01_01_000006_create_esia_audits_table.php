<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('type', ['esia', 'environmental_audit', 'social_audit', 'ohs_audit', 'compliance_audit'])->default('esia');
            $table->string('reference_number')->nullable();
            $table->date('assessment_date')->nullable();
            $table->foreignId('lead_assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('findings_summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('report_file')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_audits');
    }
};
