<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Core investigation record - one incident can have multiple
        // investigation records (e.g. a 5 Whys + a Fishbone for a moderate incident).
        Schema::create('incident_investigations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();

            $table->enum('method', [
                'five_whys',
                'fishbone',
                'taprout',
                'barrier',
            ]);

            // ---- 5 Whys fields ----------------------------------------
            $table->text('why_1')->nullable();
            $table->text('why_2')->nullable();
            $table->text('why_3')->nullable();
            $table->text('why_4')->nullable();
            $table->text('why_5')->nullable();

            // ---- Common / shared fields ---------------------------------
            $table->text('root_cause')->nullable();
            $table->text('recommendations')->nullable();

            // ---- TapRooT fields ----------------------------------------
            // Stored as JSON arrays for flexibility.
            $table->json('timeline_events')->nullable()
                ->comment('[{time, event, description}] for TapRooT timeline reconstruction');
            $table->text('witness_statements')->nullable();
            $table->text('direct_causes')->nullable();
            $table->text('contributing_factors')->nullable();
            $table->text('action_plan')->nullable();
            $table->text('verification_notes')->nullable();
            $table->date('verification_date')->nullable();

            // ---- Action tracking (all methods) --------------------------
            $table->foreignId('responsible_person_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_date')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'completed', 'verified'])->default('draft');

            // Evidence files (comma-separated paths or JSON array of paths)
            $table->json('evidence_files')->nullable();

            $table->foreignId('conducted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ---- Fishbone cause entries (one row per cause per category) ---
        Schema::create('investigation_fishbone_causes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')
                ->constrained('incident_investigations')
                ->cascadeOnDelete();

            $table->enum('category', [
                'people',
                'equipment',
                'method',
                'materials',
                'environment',
                'management',
            ]);

            $table->text('cause');
            $table->timestamps();
        });

        // ---- Barrier Analysis rows (one per hazard/control pair) -------
        Schema::create('investigation_barrier_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')
                ->constrained('incident_investigations')
                ->cascadeOnDelete();

            $table->string('hazard');
            $table->string('required_barrier')->nullable();
            $table->enum('barrier_status', ['in_place', 'missing', 'failed', 'not_worn', 'not_implemented'])
                ->default('missing');
            $table->text('control_failure')->nullable();
            $table->text('corrective_action')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_barrier_items');
        Schema::dropIfExists('investigation_fishbone_causes');
        Schema::dropIfExists('incident_investigations');
    }
};
