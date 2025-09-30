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
        Schema::create('service_content_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('icon_class'); // Untuk menyimpan class Font Awesome, cth: 'fa-solid fa-book-bible'
            $table->text('description');
            $table->string('link')->nullable(); // Link jika ada halaman detail
            $table->integer('order')->default(0); // Untuk pengurutan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_content_settings');
    }
};
