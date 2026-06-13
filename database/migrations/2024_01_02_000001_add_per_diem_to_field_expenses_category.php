<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL/MariaDB requires raw SQL to modify an existing ENUM column.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite stores enums as CHECK constraints created at table-creation
            // time; skip here (recreate the table if running on SQLite).
            return;
        }

        DB::statement("ALTER TABLE field_expenses MODIFY COLUMN category ENUM(
            'transport',
            'accommodation',
            'meals',
            'fuel',
            'per_diem',
            'supplies',
            'communication',
            'other'
        ) NOT NULL DEFAULT 'other'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Revert: any rows using 'per_diem' should be reassigned before
        // dropping the value, otherwise this statement will fail.
        DB::statement("UPDATE field_expenses SET category = 'other' WHERE category = 'per_diem'");

        DB::statement("ALTER TABLE field_expenses MODIFY COLUMN category ENUM(
            'transport',
            'accommodation',
            'meals',
            'fuel',
            'supplies',
            'communication',
            'other'
        ) NOT NULL DEFAULT 'other'");
    }
};
