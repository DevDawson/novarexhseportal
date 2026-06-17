<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_project_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('esia_ref_number', 50)->nullable();
            $table->enum('project_type', [
                'residential', 'commercial', 'industrial', 'infrastructure',
                'mining', 'agriculture', 'energy', 'tourism', 'waste_management', 'other',
            ])->default('industrial');
            $table->string('proponent_name', 255);
            $table->string('proponent_contact', 255)->nullable();
            $table->string('proponent_address', 255)->nullable();
            $table->string('project_location', 255)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->decimal('project_area_ha', 12, 2)->nullable();
            $table->decimal('estimated_investment', 16, 2)->nullable();
            $table->date('proposed_start_date')->nullable();
            $table->date('proposed_end_date')->nullable();
            $table->enum('esia_class', ['A', 'B', 'C', 'exempt'])->default('A');
            $table->boolean('esia_required')->default(true);
            $table->string('lead_consultant', 255)->nullable();
            $table->string('lead_consultant_contact', 255)->nullable();
            $table->enum('registration_status', [
                'draft', 'submitted', 'under_review', 'approved', 'rejected',
            ])->default('draft');
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('registered_at')->nullable();
            $table->text('project_objectives')->nullable();
            $table->text('project_components')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_project_registrations');
    }
};
