<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('report_title', 255);
            $table->enum('report_type', ['screening_report', 'scoping_report', 'draft_esia', 'final_esia', 'esmp', 'audit_report'])
                ->default('draft_esia');
            $table->string('version', 20)->default('1.0');
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_prepared')->nullable();
            $table->text('executive_summary')->nullable();
            $table->string('document_file')->nullable();
            $table->enum('status', ['draft', 'peer_review', 'final', 'submitted', 'approved', 'rejected'])
                ->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('review_date')->nullable();
            $table->text('review_comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_reports');
    }
};
