<?php

namespace App\Services;

use App\Repositories\Contracts\AssetRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Support\Facades\Log;

class DocumentDataCorrectionService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected AssetRepositoryInterface $assetRepository,
        protected LocationRepositoryInterface $locationRepository,
        protected OrganizationRepositoryInterface $organizationRepository
    ) {}

    public function addLocation(array $data, int $eventIndex): array
    {
        $newLocation = [
            'original_name' => 'Lokasi Baru',
            'name' => '',
            'location_id' => null,
            'match_status' => 'unmatched',
            'similarity_score' => 0,
            'source' => 'location',
        ];
        $locations = data_get($data, "events.{$eventIndex}.location_data", []);
        $locations[] = $newLocation;
        data_set($data, "events.{$eventIndex}.location_data", $locations);
        return $data;
    }

    public function removeLocation(array $data, int $eventIndex, int $locIndex): array
    {
        $locations = data_get($data, "events.{$eventIndex}.location_data", []);
        if (isset($locations[$locIndex])) {
            array_splice($locations, $locIndex, 1);
            data_set($data, "events.{$eventIndex}.location_data", array_values($locations));
        }
        return $data;
    }

    public function updateLocation(array $data, int $eventIndex, int $locIndex, int $locationId): array
    {
        $location = $this->locationRepository->findById($locationId);
        if ($location) {
            data_set($data, "events.{$eventIndex}.location_data.{$locIndex}.name", $location->name);
            data_set($data, "events.{$eventIndex}.location_data.{$locIndex}.location_id", $location->id);
            data_set($data, "events.{$eventIndex}.location_data.{$locIndex}.match_status", 'matched');

            Log::debug('Document correction: location updated.', [
                'event_index' => $eventIndex,
                'location_id' => $location->id,
                'location_name' => $location->name,
            ]);
        }
        return $data;
    }

    public function addDateRange(array $data, int $eventIndex): array
    {
        $dates = data_get($data, "events.{$eventIndex}.dates", []);
        $dates[] = ['start' => null, 'end' => null];
        data_set($data, "events.{$eventIndex}.dates", $dates);
        return $data;
    }

    public function removeDateRange(array $data, int $eventIndex, int $dateIndex): array
    {
        $dates = data_get($data, "events.{$eventIndex}.dates", []);
        if (count($dates) > 1 && isset($dates[$dateIndex])) {
            array_splice($dates, $dateIndex, 1);
            data_set($data, "events.{$eventIndex}.dates", array_values($dates));
        }
        return $data;
    }

    public function removeItem(array $data, string $collectionKey, int $eventIndex, int $itemIndex): array
    {
        $path = "events.{$eventIndex}.{$collectionKey}";
        $collection = data_get($data, $path, []);
        if (isset($collection[$itemIndex])) {
            array_splice($collection, $itemIndex, 1);
            data_set($data, $path, array_values($collection));
            flash()->success(ucfirst($collectionKey) . ' telah dihapus dari daftar.');
        }
        return $data;
    }

    public function linkItem(array $data, string $collectionKey, $masterId, $eventIndex = null, $itemIndex = null): array
    {
        if (empty($masterId)) return $data;

        $modelClass = match ($collectionKey) {
            'equipment' => $this->assetRepository,
            'emitter_organizations' => $this->organizationRepository,
            'location_data' => $this->locationRepository,
            default => null,
        };
        if (!$modelClass) return $data;

        $masterItem = $modelClass->findById($masterId);
        if (!$masterItem) return $data;

        $path = match ($collectionKey) {
            'equipment' => "events.{$eventIndex}.equipment.{$itemIndex}",
            'emitter_organizations' => "document_information.emitter_organizations.{$itemIndex}",
            'location_data' => "events.{$eventIndex}.location_data.{$itemIndex}",
            default => null,
        };
        if (!$path) return $data;

        data_set($data, "{$path}.match_status", 'matched');
        data_set($data, "{$path}.item_id", $masterItem->id);
        data_set($data, "{$path}.name", $masterItem->name);

        Log::debug('Document correction: item linked.', [
            'collection' => $collectionKey,
            'item_id' => $masterItem->id,
            'item_name' => $masterItem->name,
        ]);

        flash()->success(ucfirst($collectionKey) . ' berhasil ditautkan.');
        return $data;
    }
}
