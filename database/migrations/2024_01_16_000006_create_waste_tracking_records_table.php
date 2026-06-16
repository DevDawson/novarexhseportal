<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_tracking_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->enum('waste_type', ['hazardous', 'non_hazardous', 'recyclable', 'clinical', 'e_waste', 'liquid', 'solid'])->default('non_hazardous');
            $table->string('waste_description');
            $table->decimal('quantity', 10, 2);
            $table->enum('unit', ['kg', 'tonnes', 'litres', 'm3', 'bags', 'drums', 'pieces'])->default('kg');
            $table->date('generation_date');
            $table->enum('disposal_method', ['recycling', 'landfill', 'incineration', 'treatment', 'recovery', 'composting', 'reuse', 'other'])->default('landfill');
            $table->string('disposal_facility')->nullable();
            $table->string('transporter')->nullable();
            $table->string('manifest_number')->nullable();
            $table->foreignId('recorded_by_id')->constrained('users');
            $table->date('disposal_date')->nullable();
            $table->enum('status', ['generated', 'stored', 'disposed'])->default('generated');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_tracking_records');
    }
};
