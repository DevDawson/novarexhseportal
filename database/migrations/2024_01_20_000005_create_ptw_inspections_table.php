<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptw_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permit_to_work_id')->constrained('permit_to_works')->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('users');
            $table->dateTime('inspected_at');
            $table->text('findings')->nullable();
            $table->unsignedTinyInteger('compliance_score')->nullable()->comment('0–100');
            $table->text('corrective_actions')->nullable();
            $table->boolean('is_compliant')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptw_inspections');
    }
};
