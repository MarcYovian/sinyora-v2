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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('email')->nullable()->after('analysis_result');
            $table->string('phone')->nullable()->after('email');
            $table->string('subject')->nullable()->after('phone');
            $table->string('city')->nullable()->after('subject');
            $table->date('doc_date')->nullable()->after('city');
            $table->nullableMorphs('detailable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('phone');
            $table->dropColumn('subject');
            $table->dropColumn('city');
            $table->dropColumn('doc_date');
            $table->dropMorphs('detailable');
        });
    }
};
