<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_register', function (Blueprint $table) {
            $table->id();

            $table->string('requirement_title');

            $table->enum('requirement_type', [
                'law', 'regulation', 'permit_license', 'client_requirement', 'other',
            ]);

            $table->string('issuing_authority')->nullable();
            $table->text('applicable_to')->nullable();

            $table->enum('compliance_status', [
                'compliant', 'non_compliant', 'partially_compliant', 'not_assessed',
            ])->default('not_assessed');

            $table->string('evidence_file')->nullable();

            $table->date('expiry_date')->nullable();
            $table->date('last_review_date')->nullable();
            $table->date('next_review_date')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_register');
    }
};
