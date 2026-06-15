<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('internal_audit_id')
                ->constrained('internal_audits')
                ->cascadeOnDelete();

            $table->string('clause_reference')->nullable();

            $table->enum('finding_type', [
                'conformity',
                'observation',
                'minor_nonconformity',
                'major_nonconformity',
                'opportunity_for_improvement',
            ]);

            $table->text('description');
            $table->text('evidence')->nullable();
            $table->text('corrective_action')->nullable();

            $table->foreignId('responsible_person_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('target_date')->nullable();

            $table->text('verification_notes')->nullable();
            $table->date('verification_date')->nullable();

            $table->enum('status', ['open', 'action_planned', 'closed', 'verified'])
                ->default('open');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_findings');
    }
};
