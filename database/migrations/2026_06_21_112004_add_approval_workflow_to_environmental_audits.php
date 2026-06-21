<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Extended approval workflow columns on environmental_audits ──
        Schema::table('environmental_audits', function (Blueprint $table) {
            // Step 17 multi-stage electronic signatures
            $table->foreignId('lead_auditor_signed_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('lead_auditor_signed_at')->nullable()->after('lead_auditor_signed_by');
            $table->text('lead_auditor_comments')->nullable()->after('lead_auditor_signed_at');

            $table->foreignId('pm_approved_by')->nullable()->after('lead_auditor_comments')->constrained('users')->nullOnDelete();
            $table->timestamp('pm_approved_at')->nullable()->after('pm_approved_by');
            $table->text('pm_comments')->nullable()->after('pm_approved_at');

            $table->foreignId('client_approved_by')->nullable()->after('pm_comments')->constrained('users')->nullOnDelete();
            $table->timestamp('client_approved_at')->nullable()->after('client_approved_by');
            $table->text('client_comments')->nullable()->after('client_approved_at');

            $table->foreignId('final_approved_by')->nullable()->after('client_comments')->constrained('users')->nullOnDelete();
            $table->timestamp('final_approved_at')->nullable()->after('final_approved_by');
            $table->text('final_comments')->nullable()->after('final_approved_at');

            // Audit workflow status now supports approval stages (stored as string to avoid enum alteration)
            $table->string('approval_status')->default('draft')->after('final_comments');
            // Values: draft | submitted | lead_auditor_signed | pm_approved | client_approved | final_approved | rejected

            $table->text('rejection_reason')->nullable()->after('approval_status');

            // KPI 7: operating hours for the period (entered by auditor)
            $table->decimal('total_operating_hours', 10, 2)->nullable()->after('rejection_reason');

            // KPI 2: number of planned audits for the period
            $table->unsignedSmallInteger('planned_audits_count')->default(0)->after('total_operating_hours');
        });

        // ── Approval history / audit trail table ──────────────────────
        Schema::create('env_audit_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('environmental_audits')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('stage'); // submitted | lead_auditor_signed | pm_approved | client_approved | final_approved | rejected
            $table->enum('action', ['approved', 'rejected']);
            $table->text('comments')->nullable();
            $table->string('signature_text'); // user name recorded as e-signature
            $table->timestamp('signed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('env_audit_approval_logs');

        Schema::table('environmental_audits', function (Blueprint $table) {
            $table->dropColumn([
                'lead_auditor_signed_by', 'lead_auditor_signed_at', 'lead_auditor_comments',
                'pm_approved_by', 'pm_approved_at', 'pm_comments',
                'client_approved_by', 'client_approved_at', 'client_comments',
                'final_approved_by', 'final_approved_at', 'final_comments',
                'approval_status', 'rejection_reason',
                'total_operating_hours', 'planned_audits_count',
            ]);
        });
    }
};
