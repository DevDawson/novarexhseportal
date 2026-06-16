<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_document_id')->constrained('corporate_documents')->cascadeOnDelete();
            $table->string('revision_number');
            $table->text('revision_reason');
            $table->foreignId('revised_by_id')->constrained('users');
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('revision_date');
            $table->date('approved_date')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'under_review', 'approved', 'superseded'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('corporate_documents', function (Blueprint $table) {
            $table->string('current_revision')->nullable()->after('status');
            $table->string('document_owner')->nullable()->after('current_revision');
            $table->string('distribution_list')->nullable()->after('document_owner');
            $table->date('next_review_date')->nullable()->after('distribution_list');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_revisions');
        Schema::table('corporate_documents', function (Blueprint $table) {
            $table->dropColumn(['current_revision', 'document_owner', 'distribution_list', 'next_review_date']);
        });
    }
};
