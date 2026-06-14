<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            // Skills Development Levy (SDL) - 4.5% of gross, employer contribution
            // payable to VETA. Not deducted from employee's net salary.
            $table->decimal('sdl', 15, 2)->default(0)->after('wcf')
                ->comment('SDL: Skills Development Levy = 4.5% of gross (employer, paid to VETA)');
        });
    }

    public function down(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $table->dropColumn('sdl');
        });
    }
};
