<?php

namespace App\Observers;

use App\Models\Asset;
use App\Services\ImageService;
use Illuminate\Support\Facades\Log;

class AssetObserver
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        Log::info('Asset created', [
            'id' => $asset->id,
            'name' => $asset->name,
            'code' => $asset->code,
            'category_id' => $asset->asset_category_id,
            'quantity' => $asset->quantity,
            'has_image' => !empty($asset->image),
            'created_by' => $asset->created_by ?? auth()->id(),
        ]);
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        Log::info('Asset updated', [
            'id' => $asset->id,
            'name' => $asset->name,
            'changes' => $asset->getChanges(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the Asset "deleted" event.
     */
    public function deleted(Asset $asset): void
    {
        // Clean up the image file using ImageService
        if ($asset->image) {
            $this->imageService->delete($asset->image);
            Log::info('Asset image cleaned up on delete via ImageService', [
                'asset_id' => $asset->id,
                'image_path' => $asset->image,
            ]);
        }

        Log::info('Asset deleted', [
            'id' => $asset->id,
            'name' => $asset->name,
            'deleted_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the Asset "restored" event.
     */
    public function restored(Asset $asset): void
    {
        Log::info('Asset restored', [
            'id' => $asset->id,
            'name' => $asset->name,
        ]);
    }

    /**
     * Handle the Asset "force deleted" event.
     */
    public function forceDeleted(Asset $asset): void
    {
        // Clean up the image file using ImageService
        if ($asset->image) {
            $this->imageService->delete($asset->image);
        }

        Log::info('Asset force deleted', [
            'id' => $asset->id,
            'name' => $asset->name,
        ]);
    }
}
