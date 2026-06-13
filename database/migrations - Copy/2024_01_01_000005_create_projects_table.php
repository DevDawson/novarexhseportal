<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('project_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('service_type', ['esia', 'environmental_audit', 'social_audit', 'training', 'monitoring', 'consultancy', 'other'])->default('other');
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('contract_value', 15, 2)->default(0);
            $table->string('location')->nullable();
            $table->enum('status', ['planning', 'ongoing', 'completed', 'on_hold', 'cancelled'])->default('planning');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
