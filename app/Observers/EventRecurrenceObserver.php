<?php

namespace App\Observers;

use App\Livewire\Pages\Event\UpcomingEvents;
use App\Models\EventRecurrence;
use Illuminate\Support\Facades\Log;

class EventRecurrenceObserver
{
    /**
     * Handle the EventRecurrence "created" event.
     */
    public function created(EventRecurrence $eventRecurrence): void
    {
        $this->clearUpcomingEventsCache();
        Log::debug('EventRecurrence cache cleared: new recurrence created', ['id' => $eventRecurrence->id]);
    }

    /**
     * Handle the EventRecurrence "updated" event.
     */
    public function updated(EventRecurrence $eventRecurrence): void
    {
        $this->clearUpcomingEventsCache();
        Log::debug('EventRecurrence cache cleared: recurrence updated', ['id' => $eventRecurrence->id]);
    }

    /**
     * Handle the EventRecurrence "deleted" event.
     */
    public function deleted(EventRecurrence $eventRecurrence): void
    {
        $this->clearUpcomingEventsCache();
        Log::debug('EventRecurrence cache cleared: recurrence deleted', ['id' => $eventRecurrence->id]);
    }

    /**
     * Handle the EventRecurrence "restored" event.
     */
    public function restored(EventRecurrence $eventRecurrence): void
    {
        $this->clearUpcomingEventsCache();
        Log::debug('EventRecurrence cache cleared: recurrence restored', ['id' => $eventRecurrence->id]);
    }

    /**
     * Handle the EventRecurrence "force deleted" event.
     */
    public function forceDeleted(EventRecurrence $eventRecurrence): void
    {
        $this->clearUpcomingEventsCache();
        Log::debug('EventRecurrence cache cleared: recurrence force deleted', ['id' => $eventRecurrence->id]);
    }

    /**
     * Clear the upcoming events cache from homepage component.
     */
    private function clearUpcomingEventsCache(): void
    {
        UpcomingEvents::clearCache();
    }
}
