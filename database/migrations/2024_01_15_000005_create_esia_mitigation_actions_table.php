<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_mitigation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('impact_id')->nullable()->constrained('esia_impact_assessments')->nullOnDelete();
            $table->enum('mitigation_type', ['avoid', 'minimize', 'restore', 'offset', 'enhance'])
                ->default('minimize');
            $table->string('activity_description', 500)->comment('What will be done');
            $table->enum('phase', ['pre_construction', 'construction', 'operation', 'decommissioning', 'all'])
                ->default('construction');
            $table->string('responsible_party', 255)->nullable();
            $table->date('timeline_start')->nullable();
            $table->date('timeline_end')->nullable();
            $table->decimal('estimated_cost', 14, 2)->nullable();
            $table->string('cost_currency', 10)->default('TZS');
            $table->string('kpi', 255)->nullable()->comment('Key Performance Indicator / success measure');
            $table->string('monitoring_frequency', 100)->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'overdue', 'cancelled'])
                ->default('planned');
            $table->date('actual_completion_date')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_mitigation_actions');
    }
};
