<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', ['policy', 'certificate', 'license', 'manual', 'sop', 'other'])->default('other');
            $table->string('document_number')->nullable();
            $table->string('file_path');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'expired', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_documents');
    }
};
