<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environmental_permits', function (Blueprint $table) {
            $table->id();
            $table->string('permit_number')->unique();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->enum('permit_type', ['emission', 'discharge', 'waste_disposal', 'water_abstraction', 'land_use', 'noise', 'eia_certificate', 'operating_license', 'other'])->default('other');
            $table->string('issuing_authority');
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->text('permit_conditions')->nullable();
            $table->enum('status', ['active', 'expired', 'suspended', 'revoked', 'under_renewal'])->default('active');
            $table->string('document_path')->nullable();
            $table->foreignId('responsible_officer_id')->constrained('users');
            $table->unsignedSmallInteger('renewal_reminder_days')->default(90);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_permits');
    }
};
