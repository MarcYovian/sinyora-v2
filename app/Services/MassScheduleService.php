<?php

namespace App\Services;

use App\Repositories\Contracts\MassScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MassScheduleService
{
    // protected const CACHE_TAG = 'mass_schedules';
    protected const CACHE_KEY_ALL_PUBLIC = 'mass_schedules.all_public'; // Key baru untuk data publik
    protected const CACHE_KEY_ID = 'mass_schedules.id.';

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected MassScheduleRepositoryInterface $massScheduleRepository,
    ) {}

    public function getSchedulesForPublic()
    {
        return Cache::rememberForever(self::CACHE_KEY_ALL_PUBLIC, function () {
            try {
                return $this->massScheduleRepository->all();
            } catch (ModelNotFoundException $e) {
                return collect();
            } catch (\Exception $e) {
                Log::error('Error retrieving mass schedules for caching: ' . $e->getMessage());
                throw new \Exception("An error occurred while retrieving mass schedules.");
            }
        });
    }

    public function getSchedulesForAdmin()
    {
        return $this->massScheduleRepository->all();
    }

    public function create(array $data)
    {
        try {
            $result = $this->massScheduleRepository->create($data);
            $this->clearCache();
            return $result;
        } catch (\Exception $e) {
            Log::error('Error creating mass schedule: ' . $e->getMessage());
            throw new \Exception("An error occurred while creating the mass schedule. Please try again later.");
        }
    }

    public function find(int $id)
    {
        $cacheKey = self::CACHE_KEY_ID . $id;

        return Cache::rememberForever($cacheKey, function () use ($id) {
            try {
                return $this->massScheduleRepository->find($id);
            } catch (ModelNotFoundException $e) {
                throw new ModelNotFoundException("Mass schedule with ID {$id} not found.");
            } catch (\Exception $e) {
                Log::error("Error finding mass schedule with ID {$id} for caching: " . $e->getMessage());
                throw new \Exception("An error occurred while finding the mass schedule.");
            }
        });
    }

    public function update(int $id, array $data)
    {
        try {
            $massSchedule = $this->massScheduleRepository->find($id);
            if (!$massSchedule) {
                throw new ModelNotFoundException("Mass schedule with ID {$id} not found.");
            }
            $result = $this->massScheduleRepository->update($massSchedule, $data);
            $this->clearCache($id);
            return $result;
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Mass schedule with ID {$id} not found.");
        } catch (\Exception $e) {
            Log::error("Error updating mass schedule with ID {$id}: " . $e->getMessage());
            throw new \Exception("An error occurred while updating the mass schedule. Please try again later.");
        }
    }

    public function delete(int $id)
    {
        try {
            $massSchedule = $this->massScheduleRepository->find($id);
            if (!$massSchedule) {
                throw new ModelNotFoundException("Mass schedule with ID {$id} not found.");
            }
            $result = $this->massScheduleRepository->delete($massSchedule);
            $this->clearCache($id);
            return $result;
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Mass schedule with ID {$id} not found.");
        } catch (\Exception $e) {
            Log::error("Error deleting mass schedule with ID {$id}: " . $e->getMessage());
            throw new \Exception("An error occurred while deleting the mass schedule. Please try again later.");
        }
    }

    private function clearCache(int $id = null): void
    {
        // Selalu hapus cache untuk daftar semua jadwal
        Cache::forget(self::CACHE_KEY_ALL_PUBLIC);

        // Jika ID diberikan (untuk update/delete), hapus juga cache untuk item spesifik tersebut
        if ($id) {
            Cache::forget(self::CACHE_KEY_ID . $id);
        }
    }
}
