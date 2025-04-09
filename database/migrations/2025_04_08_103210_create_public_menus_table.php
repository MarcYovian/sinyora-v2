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
        Schema::create('public_menus', function (Blueprint $table) {
            $table->id();
            $table->string('main_menu')->nullable(); // untuk dropdown jika sama
            $table->string('menu'); // nama menu
            $table->string('link')->nullable(); // bisa route_name, URL, anchor
            $table->enum('link_type', ['route', 'url', 'anchor'])->default('route');
            $table->string('link_anchor')->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->string('icon')->nullable(); // kalau ingin pakai icon juga
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_menus');
    }
};
