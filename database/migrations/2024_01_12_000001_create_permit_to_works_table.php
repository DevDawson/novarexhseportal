<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_to_works', function (Blueprint $table) {
            $table->id();
            $table->string('permit_number')->unique();

            $table->enum('permit_type', [
                'hot_work',
                'confined_space',
                'working_at_height',
                'electrical_isolation',
                'excavation',
                'lifting_operations',
                'cold_work',
                'general',
            ]);

            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('location');
            $table->text('description');

            $table->dateTime('valid_from');
            $table->dateTime('valid_to');

            // --- People ---------------------------------------------------
            // Permit Holder / Performer - the person(s)/team doing the work.
            $table->foreignId('requested_by')->constrained('users');

            // Authorizer / Issuer - approves and issues the permit.
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();

            // Area Owner / Safety Officer - co-signs for high-risk permits.
            $table->foreignId('area_authority_id')->nullable()->constrained('users')->nullOnDelete();

            // --- Hazards & Controls -----------------------------------------
            $table->text('hazards_identified')->nullable();
            $table->text('precautions_taken')->nullable();

            // JSON array of selected PPE items, e.g. ["hard_hat", "safety_glasses", ...]
            $table->json('ppe_required')->nullable();

            $table->text('emergency_procedures')->nullable();

            // --- Isolation (Electrical Isolation / Confined Space / Hot Work) ---
            $table->boolean('isolation_required')->default(false);
            $table->text('isolation_details')->nullable();

            // --- Gas Testing (Confined Space / Hot Work) -----------------------
            $table->boolean('gas_test_required')->default(false);

            // JSON: {o2, lel, h2s, co, tested_at, tested_by}
            $table->json('gas_test_results')->nullable();

            // --- Workflow ---------------------------------------------------
            $table->enum('status', [
                'draft',
                'submitted',
                'approved',
                'active',
                'suspended',
                'closed',
                'cancelled',
                'expired',
            ])->default('draft');

            $table->text('suspension_reason')->nullable();

            // --- Closeout -----------------------------------------------------
            $table->text('closeout_notes')->nullable();
            $table->foreignId('closeout_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closeout_at')->nullable();

            $table->timestamps();

            $table->index(['permit_type', 'status']);
            $table->index(['valid_from', 'valid_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_to_works');
    }
};
