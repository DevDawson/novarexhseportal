<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->date('incident_date');
            $table->string('location')->nullable();
            $table->enum('incident_type', ['near_miss', 'first_aid', 'medical_treatment', 'lost_time', 'fatality', 'environmental', 'property_damage'])->default('near_miss');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->text('description');
            $table->text('immediate_action')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_actions')->nullable();
            $table->enum('status', ['open', 'investigating', 'closed'])->default('open');
            $table->date('closed_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
