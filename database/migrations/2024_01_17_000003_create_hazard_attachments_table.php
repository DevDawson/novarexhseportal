<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_register_id')->constrained('hazard_register')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->enum('attachment_type', [
                'inspection_report',
                'photograph',
                'training_record',
                'work_permit',
                'certificate',
                'test_report',
                'other',
            ])->default('other');
            $table->string('description')->nullable();
            $table->foreignId('uploaded_by_id')->constrained('users');
            $table->date('upload_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_attachments');
    }
};
