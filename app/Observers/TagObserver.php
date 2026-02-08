<?php

namespace App\Observers;

use App\Models\Tag;
use Illuminate\Support\Facades\Log;

class TagObserver
{
    /**
     * Handle the Tag "created" event.
     */
    public function created(Tag $tag): void
    {
        Log::info('Tag created', [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the Tag "updated" event.
     */
    public function updated(Tag $tag): void
    {
        Log::info('Tag updated', [
            'id' => $tag->id,
            'name' => $tag->name,
            'changes' => $tag->getChanges(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the Tag "deleted" event.
     */
    public function deleted(Tag $tag): void
    {
        Log::info('Tag deleted', [
            'id' => $tag->id,
            'name' => $tag->name,
            'deleted_by' => auth()->id(),
        ]);
    }

    /**
     * Handle the Tag "restored" event.
     */
    public function restored(Tag $tag): void
    {
        Log::info('Tag restored', [
            'id' => $tag->id,
            'name' => $tag->name,
        ]);
    }

    /**
     * Handle the Tag "force deleted" event.
     */
    public function forceDeleted(Tag $tag): void
    {
        Log::info('Tag force deleted', [
            'id' => $tag->id,
            'name' => $tag->name,
        ]);
    }
}
