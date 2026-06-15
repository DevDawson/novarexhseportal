<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_team_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('internal_audit_id')
                ->constrained('internal_audits')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['internal_audit_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_team_members');
    }
};
