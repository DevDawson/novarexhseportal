<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_audits', function (Blueprint $table) {
            $table->id();

            $table->string('audit_reference')->unique();

            $table->enum('audit_type', [
                'internal',
                'external',
                'certification',
                'surveillance',
                'supplier',
            ]);

            $table->enum('standard', [
                'iso9001',
                'iso14001',
                'iso45001',
                'client_specific',
                'other',
            ]);

            $table->string('standard_other')->nullable();

            $table->text('scope');

            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->date('audit_date');

            $table->foreignId('lead_auditor_id')
                ->constrained('users');

            $table->enum('status', ['planned', 'in_progress', 'completed', 'closed'])
                ->default('planned');

            $table->text('summary')->nullable();

            $table->string('report_file')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_audits');
    }
};
