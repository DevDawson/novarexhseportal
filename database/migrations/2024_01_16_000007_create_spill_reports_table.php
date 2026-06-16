<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spill_reports', function (Blueprint $table) {
            $table->id();
            $table->string('spill_reference')->unique();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('reported_by_id')->constrained('users');
            $table->date('spill_date');
            $table->string('location');
            $table->string('substance_spilled');
            $table->enum('substance_type', ['oil', 'chemical', 'fuel', 'sewage', 'acid', 'paint', 'solvent', 'other'])->default('other');
            $table->decimal('estimated_volume', 10, 2)->nullable();
            $table->enum('volume_unit', ['litres', 'm3', 'kg', 'tonnes', 'other'])->default('litres');
            $table->enum('environmental_media_affected', ['soil', 'water', 'air', 'multiple', 'none'])->default('soil');
            $table->text('cause');
            $table->text('immediate_actions')->nullable();
            $table->text('containment_actions')->nullable();
            $table->text('cleanup_actions')->nullable();
            $table->boolean('regulatory_notification_required')->default(false);
            $table->dateTime('regulatory_notified_at')->nullable();
            $table->enum('status', ['reported', 'contained', 'cleaned_up', 'closed'])->default('reported');
            $table->foreignId('closed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spill_reports');
    }
};
