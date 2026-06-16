<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptw_isolation_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permit_to_work_id')->constrained('permit_to_works')->cascadeOnDelete();
            $table->string('equipment_tag');
            $table->string('equipment_description')->nullable();
            $table->enum('isolation_type', [
                'electrical', 'mechanical', 'pneumatic', 'hydraulic', 'thermal', 'gravity', 'other',
            ])->default('electrical');
            $table->string('isolation_point')->nullable()->comment('Valve, breaker, or disconnect point');
            $table->foreignId('locked_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('lock_applied_at')->nullable();
            $table->string('key_number', 100)->nullable()->comment('LOTO lock or tag number');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('released_at')->nullable();
            $table->foreignId('released_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('release_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptw_isolation_records');
    }
};
