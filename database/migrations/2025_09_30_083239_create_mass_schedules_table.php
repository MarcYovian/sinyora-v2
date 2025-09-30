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
        Schema::create('mass_schedules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week')->comment('0=Minggu, 1=Senin, ..., 6=Sabtu');
            $table->time('start_time');
            $table->string('label')->comment('Cth: Misa 1, Misa Pagi');
            $table->string('description')->nullable()->comment('Cth: Misa Umum, Misa Lansia');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mass_schedules');
    }
};
