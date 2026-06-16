<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('certification_name');
            $table->string('issuing_body');
            $table->string('certificate_number')->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['valid', 'expired', 'suspended', 'revoked'])->default('valid');
            $table->string('document_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
