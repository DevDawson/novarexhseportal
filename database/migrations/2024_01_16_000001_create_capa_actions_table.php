<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capa_actions', function (Blueprint $table) {
            $table->id();
            $table->string('capa_reference')->unique();
            $table->enum('capa_type', ['corrective', 'preventive'])->default('corrective');
            $table->enum('source_type', ['incident', 'audit', 'inspection', 'risk_assessment', 'compliance_finding', 'other'])->default('other');
            $table->foreignId('incident_id')->nullable()->constrained('incidents')->nullOnDelete();
            $table->foreignId('audit_id')->nullable()->constrained('internal_audits')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('root_cause')->nullable();
            $table->enum('category', ['safety', 'environmental', 'quality', 'process', 'compliance', 'other'])->default('safety');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->foreignId('raised_by_id')->constrained('users');
            $table->foreignId('assigned_to_id')->constrained('users');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date');
            $table->date('completion_date')->nullable();
            $table->text('action_taken')->nullable();
            $table->enum('status', ['open', 'in_progress', 'pending_verification', 'closed'])->default('open');
            $table->boolean('effectiveness_verified')->default(false);
            $table->text('effectiveness_notes')->nullable();
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('verified_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capa_actions');
    }
};
