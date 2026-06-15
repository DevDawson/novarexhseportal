<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stakeholders', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->enum('category', [
                'community',
                'government',
                'ngo',
                'client',
                'supplier',
                'employee',
                'media',
                'investor',
                'other',
            ]);
            $table->string('organisation')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->tinyInteger('influence_level')->default(1)->comment('1-5');
            $table->tinyInteger('interest_level')->default(1)->comment('1-5');
            $table->enum('engagement_strategy', [
                'monitor',
                'keep_informed',
                'keep_satisfied',
                'manage_closely',
            ])->default('monitor');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stakeholders');
    }
};
