<?php

namespace App\Services\PreparationSteps;

use App\Repositories\Contracts\AssetRepositoryInterface;
use Atomescrochus\StringSimilarities\Compare;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;

class NormalizeAssetsStep implements PreparationStepInterface
{
    /**
     * Inject repository yang dibutuhkan untuk mengambil data master.
     */
    public function __construct(
        protected AssetRepositoryInterface $assetRepository
    ) {}

    public function handle(array $data, Closure $next): array
    {
        Log::info('Memulai NormalizeAssetsStep');
        try {
            $data = $this->process($data);
            Log::info('NormalizeAssetsStep selesai');
            return $next($data);
        } catch (Exception $e) {
            Log::error('NormalizeAssetsStep gagal: ' . $e->getMessage());

            $data['processing_errors'][] = [
                'step' => 'NormalizeAssetsStep',
                'error' => $e->getMessage(),
                'timestamp' => now()
            ];

            // Bisa juga set default values untuk equipment yang bermasalah
            $events = data_get($data, 'events', []);
            foreach ($events as &$event) {
                if (!empty($event['equipment'])) {
                    foreach ($event['equipment'] as &$asset) {
                        // Set default values jika belum ada
                        $asset['match_status'] = $asset['match_status'] ?? 'error';
                        $asset['item_id'] = $asset['item_id'] ?? null;
                        $asset['similarity_score'] = $asset['similarity_score'] ?? 0;
                    }
                }
            }
            data_set($data, 'events', $events);

            return $next($data);
        }
    }

    /**
     * Memproses data untuk menormalisasi nama aset.
     */
    public function process(array $data): array
    {
        // Ambil array events dari data utama
        $events = data_get($data, 'events', []);

        // Jika tidak ada events, tidak ada yang perlu diproses
        if (empty($events)) {
            return $data;
        }

        // Ambil data master sekali saja untuk efisiensi
        $masterAssets = $this->assetRepository->getActiveAssets();
        $comparison = new Compare();
        $similarityThreshold = 0.8; // Ambang batas 80%

        // Loop melalui setiap event menggunakan referensi (&)
        foreach ($events as &$event) {
            if (empty($event['equipment'])) {
                continue; // Lanjut ke event berikutnya jika tidak ada equipment
            }

            // Loop melalui setiap equipment di dalam event
            foreach ($event['equipment'] as &$asset) {
                if (!isset($asset['item']) || empty(trim($asset['item']))) {
                    continue;
                }

                // Membersihkan nilai kuantitas
                $numericQuantity = preg_replace('/[^0-9]/', '', $asset['quantity']);
                $asset['quantity'] = !empty($numericQuantity) ? (int)$numericQuantity : null;

                $asset['original_name'] = $asset['item'];
                $extractedName = strtolower(trim($asset['item']));
                $bestMatch = null;
                $highestScore = 0;

                // Lakukan perbandingan dengan setiap data master
                foreach ($masterAssets as $masterAsset) {
                    $masterAssetName = strtolower(trim($masterAsset->name));
                    $score = $comparison->jaroWinkler($extractedName, $masterAssetName);

                    if ($score > $highestScore) {
                        $highestScore = $score;
                        $bestMatch = $masterAsset;
                    }
                }

                // Tentukan status berdasarkan ambang batas
                if ($bestMatch && $highestScore >= $similarityThreshold) {
                    $asset['item_id'] = $bestMatch->id;
                    $asset['match_status'] = 'matched';
                } else {
                    $asset['item_id'] = null;
                    $asset['match_status'] = 'unmatched';
                }
                $asset['similarity_score'] = round($highestScore, 2);
            }
            unset($asset); // Hapus referensi setelah loop selesai
        }
        unset($event); // Hapus referensi setelah loop selesai

        // Set kembali array events yang sudah dimodifikasi ke dalam data utama
        data_set($data, 'events', $events);

        return $data;
    }
}
