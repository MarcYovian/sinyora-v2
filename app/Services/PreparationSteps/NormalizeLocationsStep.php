<?php

namespace App\Services\PreparationSteps;

use App\Repositories\Contracts\LocationRepositoryInterface;
use Atomescrochus\StringSimilarities\Compare;
use Closure;
use Illuminate\Support\Facades\Log;

class NormalizeLocationsStep implements PreparationStepInterface
{
    public function __construct(
        protected LocationRepositoryInterface $locationRepository
    ) {}

    public function handle(array $data, Closure $next): array
    {
        Log::info('Memulai NormalizeLocationsStep');
        try {
            $data = $this->process($data);
            Log::info('NormalizeLocationsStep selesai');
            return $next($data);
        } catch (\Exception $e) {
            Log::error('NormalizeLocationsStep gagal: ' . $e->getMessage());

            $data['processing_errors'][] = [
                'step' => 'NormalizeLocationsStep',
                'error' => $e->getMessage(),
                'timestamp' => now()
            ];

            return $next($data);
        }
    }

    public function process(array $data): array
    {
        $events = data_get($data, 'events', []);
        if (empty($events)) {
            return $data;
        }

        $masterLocations = $this->locationRepository->getActiveLocations();
        $comparison = new Compare();
        $similarityThreshold = 0.75;

        foreach ($events as &$event) {
            if (empty($event['location'])) {
                continue;
            }

            // --- Logika Parsing Lokasi ---
            $finalLocations = [];
            // 1. Jalankan ekspansi angka terlebih dahulu
            $potentiallyExpanded = $this->expandNumericRange($event['location']);

            // 2. Untuk setiap hasilnya, jalankan pemisahan berdasarkan kata kunci
            foreach ($potentiallyExpanded as $locationPart) {
                $reconstructed = $this->splitAndReconstructLocations($locationPart);
                $finalLocations = array_merge($finalLocations, $reconstructed);
            }
            $locationsToMatch = array_unique($finalLocations);
            // --- Selesai Parsing Lokasi ---

            $event['location_data'] = [];

            // 3. Lakukan fuzzy matching untuk setiap lokasi yang ditemukan
            foreach ($locationsToMatch as $locationName) {
                $singleLocationData = ['original_name' => $locationName];
                $extractedName = strtolower(trim($locationName));
                $bestMatch = null;
                $highestScore = 0;

                foreach ($masterLocations as $masterLocation) {
                    $masterLocationName = strtolower(trim($masterLocation->name));
                    $score = $comparison->jaroWinkler($extractedName, $masterLocationName);

                    if ($score > $highestScore) {
                        $highestScore = $score;
                        $bestMatch = $masterLocation;
                    }
                }

                if ($bestMatch && $highestScore >= $similarityThreshold) {
                    $singleLocationData['name'] = $bestMatch->name;
                    $singleLocationData['location_id'] = $bestMatch->id;
                    $singleLocationData['match_status'] = 'matched';
                    $singleLocationData['source'] = 'location';
                } else {
                    $singleLocationData['name'] = $locationName; // Kembalikan nama asli jika tidak cocok
                    $singleLocationData['location_id'] = null;
                    $singleLocationData['match_status'] = 'unmatched';
                    $singleLocationData['source'] = 'custom';
                }
                $singleLocationData['similarity_score'] = round($highestScore, 2);
                $event['location_data'][] = $singleLocationData;
            }
        }
        unset($event);
        Log::info('NormalizeLocationsStep selesai:' . json_encode($events));
        data_set($data, 'events', $events);

        return $data;
    }

    /**
     * Helper untuk memecah "Ruang Rapat lantai 1 2 3".
     */
    private function expandNumericRange(string $locationString): array
    {
        $pattern = '/^(.*?\D)\s*((?:\d+\s*)+)$/';
        if (preg_match($pattern, $locationString, $matches)) {
            $prefix = trim($matches[1]);
            $numberSequence = trim($matches[2]);
            $numbers = preg_split('/\\s+/', $numberSequence);
            $expandedLocations = [];
            foreach ($numbers as $number) {
                if (!empty($number)) {
                    $expandedLocations[] = $prefix . ' ' . $number;
                }
            }
            return $expandedLocations;
        }
        return [$locationString];
    }

    /**
     * Helper untuk memecah "Aula A dan B".
     */
    private function splitAndReconstructLocations(string $locationString): array
    {
        $parts = preg_split('/\\s*(\bdan\b|&|,)\\s*/i', $locationString);
        if (count($parts) <= 1) {
            return [$locationString];
        }
        $reconstructed = [];
        $base = trim($parts[0]);
        $reconstructed[] = $base;
        $lastSpacePos = strrpos($base, ' ');
        $prefix = ($lastSpacePos !== false) ? substr($base, 0, $lastSpacePos + 1) : '';
        for ($i = 1; $i < count($parts); $i++) {
            $currentPart = trim($parts[$i]);
            if (str_starts_with(strtolower($currentPart), strtolower(trim($prefix)))) {
                $reconstructed[] = $currentPart;
            } else {
                $reconstructed[] = $prefix . $currentPart;
            }
        }
        return $reconstructed;
    }
}
