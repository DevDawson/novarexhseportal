<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->enum('currency', ['TZS', 'USD'])
                ->default('TZS')
                ->after('estimated_value');

            // Exchange rate at the time the tender value was recorded
            // (1 USD = X TZS). Used to display estimated_value in the
            // other currency without re-entering data.
            $table->decimal('exchange_rate', 12, 4)
                ->default(1)
                ->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });
    }
};
