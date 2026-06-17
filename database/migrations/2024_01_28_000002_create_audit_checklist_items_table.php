<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_checklist_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('internal_audit_id')
                ->constrained('internal_audits')
                ->cascadeOnDelete();

            $table->enum('iso_standard', ['iso9001', 'iso14001', 'iso45001', 'iso50001', 'other'])
                ->default('other');

            $table->string('clause_reference', 30)->nullable();

            $table->text('question');

            $table->enum('requirement_type', ['mandatory', 'recommended', 'optional'])
                ->default('mandatory');

            $table->enum('response', [
                'compliant',
                'non_compliant',
                'observation',
                'not_applicable',
                'not_assessed',
            ])->default('not_assessed');

            $table->tinyInteger('score')->unsigned()->nullable()
                ->comment('0=NC, 1=Minor, 2=Partial, 3=Mostly, 4=Good, 5=Full');

            $table->text('evidence_notes')->nullable();
            $table->text('auditor_notes')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_items');
    }
};
