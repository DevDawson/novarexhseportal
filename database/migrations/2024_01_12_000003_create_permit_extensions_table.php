<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permit_to_work_id')->constrained('permit_to_works')->cascadeOnDelete();
            $table->dateTime('previous_valid_to');
            $table->dateTime('extended_to');
            $table->text('reason')->nullable();
            $table->foreignId('extended_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_extensions');
    }
};
