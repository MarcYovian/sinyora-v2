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
        DB::table('documents')
            // Hanya ambil baris yang memiliki format lama (ada key 'data' di root JSON)
            ->where('analysis_result', 'like', '{"data":%')
            ->cursor() // Mengambil data satu per satu untuk menghindari memory leak
            ->each(function ($document) {
                // Decode JSON menjadi array asosiatif
                $analysisResult = json_decode($document->analysis_result, true);

                // Ambil data yang ada di dalam key 'data'
                if (isset($analysisResult['data'])) {
                    $normalizedData = $analysisResult['data'];

                    DB::table('documents')
                        ->where('id', $document->id)
                        ->update(['analysis_result' => json_encode($normalizedData)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            //
        });
    }
};
