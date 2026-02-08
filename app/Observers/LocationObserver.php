<?php

namespace App\Observers;

use App\Models\Location;
use App\Services\ImageService;
use Illuminate\Support\Facades\Log;

class LocationObserver
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    /**
     * Handle the Location "created" event.
     */
    public function created(Location $location): void
    {
        Log::info('Location created', [
            'id' => $location->id,
            'name' => $location->name,
            'has_image' => !empty($location->image),
        ]);
    }

    /**
     * Handle the Location "updated" event.
     */
    public function updated(Location $location): void
    {
        Log::info('Location updated', [
            'id' => $location->id,
            'name' => $location->name,
            'changes' => $location->getChanges(),
        ]);
    }

    /**
     * Handle the Location "deleted" event.
     */
    public function deleted(Location $location): void
    {
        // Delete associated image when location is deleted
        if ($location->image) {
            $this->imageService->delete($location->image);
            Log::info('Location image deleted', ['path' => $location->image]);
        }

        Log::info('Location deleted', [
            'id' => $location->id,
            'name' => $location->name,
        ]);
    }

    /**
     * Handle the Location "restored" event.
     */
    public function restored(Location $location): void
    {
        Log::info('Location restored', ['id' => $location->id]);
    }

    /**
     * Handle the Location "force deleted" event.
     */
    public function forceDeleted(Location $location): void
    {
        // Delete associated image on force delete
        if ($location->image) {
            $this->imageService->delete($location->image);
        }

        Log::info('Location force deleted', ['id' => $location->id]);
    }
}
