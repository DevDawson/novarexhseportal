<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esia_baseline_data', function (Blueprint $table) {
            // Specific parameter sub-type (preset from spec comprehensive list)
            $table->string('parameter_subtype', 200)->nullable()->after('parameter_name');
            // GPS sampling point
            $table->string('gps_coordinates', 100)->nullable()->after('sampling_location');
            // TZS/WHO/NEMC standard reference
            $table->string('standard_reference', 100)->nullable()->after('standard_limit');
            // Trend direction compared to previous measurement
            $table->enum('trend', ['improving', 'stable', 'worsening', 'unknown'])->default('unknown')->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('esia_baseline_data', function (Blueprint $table) {
            $table->dropColumn(['parameter_subtype', 'gps_coordinates', 'standard_reference', 'trend']);
        });
    }
};
