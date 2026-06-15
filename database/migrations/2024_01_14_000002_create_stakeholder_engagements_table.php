<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stakeholder_engagements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stakeholder_id')
                ->constrained('stakeholders')
                ->cascadeOnDelete();

            $table->date('engagement_date');

            $table->enum('method', [
                'meeting',
                'consultation',
                'survey',
                'site_visit',
                'written_communication',
                'public_hearing',
                'focus_group',
                'other',
            ]);

            $table->string('topic');
            $table->text('summary');
            $table->text('outcome')->nullable();
            $table->text('commitments_made')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->boolean('follow_up_completed')->default(false);

            $table->foreignId('conducted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stakeholder_engagements');
    }
};
