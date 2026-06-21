<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esg_maturity_assessments', function (Blueprint $table) {
            $table->id();

            // Assessment period
            $table->string('period', 20);              // e.g. "2026", "2026-Q2"
            $table->enum('period_type', ['annual', 'quarterly'])->default('annual');
            $table->string('status', 20)->default('draft'); // draft | finalized

            $table->foreignId('assessed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            $table->text('notes')->nullable();

            // ── E: Environmental Indicator Scores (0–100%) ──────────────
            $table->decimal('cr_score',  5, 2)->nullable(); // Compliance Rate (AUTO)
            $table->decimal('wr_score',  5, 2)->nullable(); // Waste Diversion Rate (AUTO)
            $table->decimal('er_score',  5, 2)->nullable(); // Emissions Reduction Rate (SEMI-AUTO)
            $table->decimal('wtr_score', 5, 2)->nullable(); // Water Reduction Efficiency (SEMI-AUTO)
            $table->decimal('ems_score', 5, 2)->nullable(); // EMS Maturity Index (AUTO)

            // ── S: Social Indicator Scores ───────────────────────────────
            $table->decimal('tr_score',    5, 2)->nullable(); // Training Completion Rate (AUTO)
            $table->decimal('ltifr_score', 5, 2)->nullable(); // LTIFR Performance Score (SEMI-AUTO)
            $table->decimal('ewr_score',   5, 2)->nullable(); // Employee Well-being Score (MANUAL)
            $table->decimal('csr_score',   5, 2)->nullable(); // Community Engagement Score (SEMI-AUTO)
            $table->decimal('dei_score',   5, 2)->nullable(); // DEI Score (SEMI-AUTO)

            // ── G: Governance Indicator Scores ───────────────────────────
            $table->decimal('ccr_score', 5, 2)->nullable(); // Compliance & Ethics Score (SEMI-AUTO)
            $table->decimal('acr_score', 5, 2)->nullable(); // Audit Closure Rate (AUTO)
            $table->decimal('dcr_score', 5, 2)->nullable(); // Document Control Compliance Rate (AUTO)
            $table->decimal('ecr_score', 5, 2)->nullable(); // Corrective Action Closure Rate (AUTO)
            $table->decimal('mrr_score', 5, 2)->nullable(); // Management Review Rate (MANUAL)

            // ── Computed composite scores ────────────────────────────────
            $table->decimal('e_score',  5, 2)->nullable(); // (CR+WR+ER+WTR+EMS) / 5
            $table->decimal('s_score',  5, 2)->nullable(); // (TR+LTIFR+EWR+CSR+DEI) / 5
            $table->decimal('g_score',  5, 2)->nullable(); // (CCR+ACR+DCR+ECR+MRR) / 5
            $table->decimal('esg_mi',   5, 2)->nullable(); // (E×40 + S×30 + G×30) / 100

            // JSON map of which scores were auto-populated
            $table->json('auto_sources')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esg_maturity_assessments');
    }
};
