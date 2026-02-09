<?php

namespace App\Observers;

use App\Livewire\Pages\Event\UpcomingEvents;
use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        $this->clearAllEventCaches();
        Log::debug('Event cache cleared: new event created', ['id' => $event->id]);
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        $this->clearAllEventCaches();
        Log::debug('Event cache cleared: event updated', ['id' => $event->id]);
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        $this->clearAllEventCaches();
        Log::debug('Event cache cleared: event deleted', ['id' => $event->id]);
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        $this->clearAllEventCaches();
        Log::debug('Event cache cleared: event restored', ['id' => $event->id]);
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        $this->clearAllEventCaches();
        Log::debug('Event cache cleared: event force deleted', ['id' => $event->id]);
    }

    /**
     * Clear all event-related caches.
     * Note: Calendar view caches use date-based keys, so we can't clear them all without pattern matching.
     * They will expire naturally (3 min TTL) or can be cleared manually if Redis is used.
     */
    private function clearAllEventCaches(): void
    {
        // Clear upcoming events cache
        UpcomingEvents::clearCache();

        // Clear dropdown caches
        Cache::forget('event_categories_dropdown');
    }
}
