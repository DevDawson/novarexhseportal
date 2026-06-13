<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            // Likelihood (L) and Impact/Severity (I), each 0-5.
            $table->unsignedTinyInteger('likelihood')->default(0)->after('severity')
                ->comment('L: 0=None ... 5=Almost Certain');

            $table->unsignedTinyInteger('impact')->default(0)->after('likelihood')
                ->comment('I: 0=No Impact ... 5=Catastrophic');

            // Risk Score = L x I (0-25). The 'severity' enum is auto-derived
            // from this score via RiskScoringService (low/medium/high/critical).
            $table->unsignedTinyInteger('risk_score')->default(0)->after('impact')
                ->comment('Risk Score = Likelihood x Impact (0-25)');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn(['likelihood', 'impact', 'risk_score']);
        });
    }
};
