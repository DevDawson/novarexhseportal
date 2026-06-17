<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('esia_impact_assessments', function (Blueprint $table) {
            // 3-level system from spec: Low (1-20) / Medium (21-80) / High (81+)
            $table->enum('impact_level', ['low', 'medium', 'high'])->default('low')->after('significance_level');
            $table->enum('residual_impact_level', ['low', 'medium', 'high'])->nullable()->after('residual_significance_level');
            // Cumulative / directness flags from Step 5 spec
            $table->boolean('is_direct')->default(true)->after('nature');
            $table->boolean('is_cumulative')->default(false)->after('is_direct');
            $table->boolean('is_reversible')->default(true)->after('is_cumulative');
        });
    }

    public function down(): void
    {
        Schema::table('esia_impact_assessments', function (Blueprint $table) {
            $table->dropColumn([
                'impact_level',
                'residual_impact_level',
                'is_direct',
                'is_cumulative',
                'is_reversible',
            ]);
        });
    }
};
