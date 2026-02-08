<?php

namespace App\Observers;

use App\Models\AssetCategory;
use Illuminate\Support\Facades\Log;

class AssetCategoryObserver
{
    /**
     * Handle the AssetCategory "created" event.
     */
    public function created(AssetCategory $category): void
    {
        Log::info('Asset category created', [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the AssetCategory "updated" event.
     */
    public function updated(AssetCategory $category): void
    {
        Log::info('Asset category updated', [
            'id' => $category->id,
            'name' => $category->name,
            'changes' => $category->getChanges(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the AssetCategory "deleted" event.
     */
    public function deleted(AssetCategory $category): void
    {
        Log::info('Asset category deleted', [
            'id' => $category->id,
            'name' => $category->name,
            'deleted_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the AssetCategory "restored" event.
     */
    public function restored(AssetCategory $category): void
    {
        Log::info('Asset category restored', [
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }

    /**
     * Handle the AssetCategory "force deleted" event.
     */
    public function forceDeleted(AssetCategory $category): void
    {
        Log::info('Asset category force deleted', [
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }
}
