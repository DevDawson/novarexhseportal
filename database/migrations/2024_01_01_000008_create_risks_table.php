<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('risk_title');
            $table->text('description')->nullable();
            $table->enum('category', ['safety', 'environmental', 'financial', 'operational', 'legal', 'reputational'])->default('safety');
            $table->unsignedTinyInteger('likelihood')->default(1); // 1-5
            $table->unsignedTinyInteger('severity')->default(1); // 1-5
            $table->unsignedTinyInteger('risk_rating')->default(1); // likelihood * severity
            $table->text('mitigation_measures')->nullable();
            $table->foreignId('risk_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'mitigated', 'closed'])->default('open');
            $table->date('review_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
