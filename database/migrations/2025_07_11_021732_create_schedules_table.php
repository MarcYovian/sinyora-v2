<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->time('start_time')->nullable(); // Nullable in case the start time is not set
            $table->time('end_time')->nullable(); // Nullable in case the end time is not set
            $table->string('duration')->nullable(); // Duration in minutes or a specific format
            $table->morphs('describable'); // Polymorphic relation to link to different models (e.g., Event, Document, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
