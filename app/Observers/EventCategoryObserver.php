<?php

namespace App\Observers;

use App\Models\EventCategory;
use Illuminate\Support\Facades\Log;

class EventCategoryObserver
{
    /**
     * Handle the EventCategory "created" event.
     */
    public function created(EventCategory $category): void
    {
        Log::info('Event category created', [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'color' => $category->color,
            'is_mass_category' => $category->is_mass_category,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the EventCategory "updated" event.
     */
    public function updated(EventCategory $category): void
    {
        Log::info('Event category updated', [
            'id' => $category->id,
            'name' => $category->name,
            'changes' => $category->getChanges(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the EventCategory "deleted" event.
     */
    public function deleted(EventCategory $category): void
    {
        Log::info('Event category deleted', [
            'id' => $category->id,
            'name' => $category->name,
            'deleted_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the EventCategory "restored" event.
     */
    public function restored(EventCategory $category): void
    {
        Log::info('Event category restored', [
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }

    /**
     * Handle the EventCategory "force deleted" event.
     */
    public function forceDeleted(EventCategory $category): void
    {
        Log::info('Event category force deleted', [
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }
}
