<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the standard enum to include ISO 50001
        DB::statement("ALTER TABLE internal_audits MODIFY COLUMN standard ENUM(
            'iso9001','iso14001','iso45001','iso50001','client_specific','other'
        ) NOT NULL");

        Schema::table('internal_audits', function (Blueprint $table) {
            $table->string('audit_location')->nullable()->after('scope');
            $table->text('auditee_representative')->nullable()->after('audit_location');
            $table->foreignId('approved_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('closure_date');
            $table->timestamp('approved_at')->nullable()->after('approved_by_id');
            $table->unsignedSmallInteger('total_findings')->default(0)->after('approved_at');
            $table->unsignedSmallInteger('open_ncs')->default(0)->after('total_findings');
            $table->decimal('compliance_score', 5, 2)->nullable()->after('open_ncs');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('internal_audits', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'audit_location', 'auditee_representative',
                'total_findings', 'open_ncs', 'compliance_score',
                'approved_at',
            ]);
            $table->dropConstrainedForeignId('approved_by_id');
        });

        DB::statement("ALTER TABLE internal_audits MODIFY COLUMN standard ENUM(
            'iso9001','iso14001','iso45001','client_specific','other'
        ) NOT NULL");
    }
};
