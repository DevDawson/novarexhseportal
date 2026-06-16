<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptw_toolbox_talks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permit_to_work_id')->constrained('permit_to_works')->cascadeOnDelete();
            $table->foreignId('conducted_by_id')->constrained('users');
            $table->dateTime('conducted_at');
            $table->text('topics_covered');
            $table->text('attendees')->nullable()->comment('Names/IDs of attendees, one per line');
            $table->unsignedSmallInteger('number_of_attendees')->default(0);
            $table->text('summary')->nullable();
            $table->text('safety_concerns_raised')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptw_toolbox_talks');
    }
};
