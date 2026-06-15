<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esia_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            // Scoring factors (1-5 each); total = scale + sensitivity + pollution_potential (max 15)
            $table->unsignedTinyInteger('scale')->default(1)->comment('1=Local 5=National/International');
            $table->unsignedTinyInteger('sensitivity')->default(1)->comment('1=Low sensitivity 5=Highly sensitive area');
            $table->unsignedTinyInteger('pollution_potential')->default(1)->comment('1=Negligible 5=High');
            $table->unsignedTinyInteger('screening_score')->default(3)->comment('scale+sensitivity+pollution_potential');
            $table->enum('category', ['A', 'B', 'C'])->default('B')
                ->comment('A=Full ESIA(>=11) B=Limited EIA(6-10) C=No EIA required(<=5)');
            $table->text('project_description')->nullable();
            $table->text('screening_justification')->nullable();
            $table->foreignId('screened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('screened_at')->nullable();
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected'])->default('pending');
            $table->text('reviewer_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esia_screenings');
    }
};
