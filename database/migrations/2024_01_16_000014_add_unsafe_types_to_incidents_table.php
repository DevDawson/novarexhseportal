<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE incidents MODIFY COLUMN incident_type ENUM(
            'near_miss',
            'unsafe_act',
            'unsafe_condition',
            'first_aid',
            'medical_treatment',
            'lost_time',
            'fatality',
            'environmental',
            'property_damage'
        ) NOT NULL DEFAULT 'near_miss'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE incidents MODIFY COLUMN incident_type ENUM(
            'near_miss',
            'first_aid',
            'medical_treatment',
            'lost_time',
            'fatality',
            'environmental',
            'property_damage'
        ) NOT NULL DEFAULT 'near_miss'");
    }
};
