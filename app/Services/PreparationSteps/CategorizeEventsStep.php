<?php

namespace App\Services\PreparationSteps;

use App\Repositories\Contracts\EventCategoryRepositoryInterface;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;

class CategorizeEventsStep implements PreparationStepInterface
{
    public function __construct(
        protected EventCategoryRepositoryInterface $eventCategoryRepository
    ) {}

    public function handle(array $data, Closure $next): array
    {
        Log::info('Memulai CategorizeEventsStep');
        try {
            $data = $this->process($data);
            Log::info('CategorizeEventsStep selesai');
            return $next($data);
        } catch (Exception $e) {
            Log::error('CategorizeEventsStep gagal: ' . $e->getMessage());

            $data['processing_errors'][] = [
                'step' => 'CategorizeEventsStep',
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

        // Ambil semua kategori dan keywords-nya dari database, sekali saja.
        $categories = $this->eventCategoryRepository->getAllWithKeywords();

        // Tentukan kategori default jika tidak ada keyword yang cocok
        $defaultCategory = [
            'name' => 'Koinonia (Persekutuan)',
            'id'   => 3 // Ganti dengan ID Koinonia yang sebenarnya di DB Anda
        ];

        foreach ($events as &$event) {
            $searchText = strtolower($event['eventName'] ?? '');
            $matchedCategory = null;

            // Loop melalui setiap kategori dari database
            foreach ($categories as $category) {
                // Asumsi: keywords adalah string dipisahkan koma.
                // Jika formatnya JSON, ganti dengan json_decode($category->keywords).
                $keywords = $category->keywords;

                foreach ($keywords as $keyword) {
                    if (str_contains($searchText, trim(strtolower($keyword)))) {
                        $matchedCategory = $category;
                        break 2; // Keluar dari 2 loop jika sudah ketemu
                    }
                }
            }

            // Tambahkan key baru 'fivetask_categories' ke array kegiatan
            if ($matchedCategory) {
                $event['fivetask_categories'] = [
                    'name' => $matchedCategory->name,
                    'id'   => $matchedCategory->id
                ];
            } else {
                $event['fivetask_categories'] = $defaultCategory;
            }
        }
        unset($event);

        data_set($data, 'events', $events);
        return $data;
    }
}
