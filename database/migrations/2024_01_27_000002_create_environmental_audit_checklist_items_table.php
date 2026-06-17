<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environmental_audit_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('environmental_audits')->cascadeOnDelete();
            $table->string('category', 2);         // A–I
            $table->string('item_code', 5);        // A1, B3, etc.
            $table->text('item_description');
            $table->enum('compliance_status', [
                'compliant', 'partially_compliant', 'non_compliant', 'not_applicable',
            ])->default('not_applicable');
            $table->text('evidence_notes')->nullable();
            $table->string('evidence_file', 500)->nullable();
            $table->text('findings_notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_audit_checklist_items');
    }
};
