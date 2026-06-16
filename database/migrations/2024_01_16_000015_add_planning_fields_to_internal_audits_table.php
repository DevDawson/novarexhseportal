<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_audits', function (Blueprint $table) {
            $table->text('audit_objectives')->nullable()->after('scope');
            $table->text('audit_criteria')->nullable()->after('audit_objectives');
            $table->date('planned_start_date')->nullable()->after('audit_date');
            $table->date('planned_end_date')->nullable()->after('planned_start_date');
            $table->text('opening_meeting_notes')->nullable()->after('summary');
            $table->text('closing_meeting_notes')->nullable()->after('opening_meeting_notes');
            $table->text('closure_verification_notes')->nullable()->after('closing_meeting_notes');
            $table->foreignId('closure_verified_by_id')->nullable()->constrained('users')->nullOnDelete()->after('closure_verification_notes');
            $table->date('closure_date')->nullable()->after('closure_verified_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('internal_audits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closure_verified_by_id');
            $table->dropColumn([
                'audit_objectives', 'audit_criteria', 'planned_start_date', 'planned_end_date',
                'opening_meeting_notes', 'closing_meeting_notes', 'closure_verification_notes', 'closure_date',
            ]);
        });
    }
};
