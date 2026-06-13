<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('document_title');
            $table->string('document_code')->nullable();
            $table->enum('document_type', ['report', 'drawing', 'plan', 'certificate', 'correspondence', 'other'])->default('report');
            $table->string('revision_no')->default('A');
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'internal_review', 'client_review', 'approved', 'superseded'])->default('draft');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('submission_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverables');
    }
};
