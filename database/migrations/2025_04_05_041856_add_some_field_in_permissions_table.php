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
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('group')->nullable()->after('name');
            $table->string('route_name')->nullable()->after('group');
            $table->string('default')->nullable()->after('route_name');
            $table->foreign('group')->references('id')->on('groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['group']);
            $table->dropColumn('group');
            $table->dropColumn('route_name');
            $table->dropColumn('default');
        });
    }
};
