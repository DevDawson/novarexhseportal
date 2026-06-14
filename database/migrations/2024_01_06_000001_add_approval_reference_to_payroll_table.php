<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $table->string('approval_reference')->nullable()->after('payment_status')
                ->comment('Reference recorded when this payroll was approved (e.g. by Finance Manager / MD)');
        });
    }

    public function down(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $table->dropColumn('approval_reference');
        });
    }
};
