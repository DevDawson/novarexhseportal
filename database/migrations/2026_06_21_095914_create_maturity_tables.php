<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Dimensions (seeded once via MaturitySeeder) ───────────────
        Schema::create('maturity_dimensions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);          // A, B, C …
            $table->string('name');
            $table->unsignedTinyInteger('weight'); // percentage 0-100
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
        });

        // ── Indicators per dimension ───────────────────────────────────
        Schema::create('maturity_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimension_id')->constrained('maturity_dimensions')->cascadeOnDelete();
            $table->string('name');
            $table->string('auto_source')->nullable(); // module key for auto-calc
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
        });

        // ── Assessment (snapshot per project + period) ─────────────────
        Schema::create('maturity_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period');              // e.g. "2026-Q2" or "2026-06"
            $table->enum('period_type', ['monthly', 'quarterly', 'annual'])->default('quarterly');
            $table->decimal('overall_score', 5, 2)->nullable(); // computed
            $table->string('maturity_level')->nullable();        // computed label
            $table->text('notes')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('assessed_at')->nullable();
            $table->enum('status', ['draft', 'finalised'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Individual indicator scores within an assessment ───────────
        Schema::create('maturity_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('maturity_assessments')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('maturity_indicators')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(1); // 1–5
            $table->boolean('auto_calculated')->default(false);
            $table->text('evidence')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'indicator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maturity_scores');
        Schema::dropIfExists('maturity_assessments');
        Schema::dropIfExists('maturity_indicators');
        Schema::dropIfExists('maturity_dimensions');
    }
};
