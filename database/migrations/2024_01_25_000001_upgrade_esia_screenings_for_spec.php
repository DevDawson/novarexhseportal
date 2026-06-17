<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esia_screenings', function (Blueprint $table) {
            $table->boolean('land_acquisition_involved')->default(false)->after('screening_justification');
            $table->boolean('biodiversity_risk_checked')->default(false)->after('land_acquisition_involved');
            $table->boolean('sensitive_area_check')->default(false)->after('biodiversity_risk_checked');
            $table->boolean('pollution_check_done')->default(false)->after('sensitive_area_check');
        });
    }

    public function down(): void
    {
        Schema::table('esia_screenings', function (Blueprint $table) {
            $table->dropColumn([
                'land_acquisition_involved',
                'biodiversity_risk_checked',
                'sensitive_area_check',
                'pollution_check_done',
            ]);
        });
    }
};
