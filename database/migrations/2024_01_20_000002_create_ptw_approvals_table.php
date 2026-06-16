<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptw_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permit_to_work_id')->constrained('permit_to_works')->cascadeOnDelete();
            $table->enum('approval_stage', ['supervisor', 'hse_officer', 'site_manager']);
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('decision', ['pending', 'approved', 'rejected', 'modification_requested'])->default('pending');
            $table->text('comments')->nullable();
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();

            $table->index(['permit_to_work_id', 'approval_stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptw_approvals');
    }
};
