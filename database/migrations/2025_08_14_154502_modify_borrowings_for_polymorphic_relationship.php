<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->morphs('borrowable');
        });

        DB::table('borrowings')
            ->whereNotNull('event_id')
            ->update([
                'borrowable_id' => DB::raw('event_id'), // Salin nilai dari kolom event_id
                'borrowable_type' => 'App\\Models\\Event'  // Tetapkan tipe modelnya
            ]);

        Schema::table('borrowings', function (Blueprint $table) {
            // Kita harus menghapus foreign key constraint terlebih dahulu.
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            // TAHAP 1 (Reverse): Tambahkan kembali kolom event_id.
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
        });

        // TAHAP 2 (Reverse): Kembalikan data dari kolom polimorfik.
        DB::table('borrowings')
            ->where('borrowable_type', 'App\\Models\\Event')
            ->update([
                'event_id' => DB::raw('borrowable_id')
            ]);

        // TAHAP 3 (Reverse): Hapus kolom polimorfik.
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropMorphs('borrowable');
        });
    }
};
