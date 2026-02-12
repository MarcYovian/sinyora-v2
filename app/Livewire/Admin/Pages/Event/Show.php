<?php

namespace App\Livewire\Admin\Pages\Event;

use App\Livewire\Forms\EventForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    #[Layout('layouts.app')]

    public EventForm $form;
    public Event $event;
    public array $mergedSchedules = [];
    public string $correlationId = '';

    public $categories;
    public $locations;
    public $organizations;

    /**
     * Mount the component.
     */
    public function mount(Event $event): void
    {
        $this->authorize('access', 'admin.events.show');
        $this->correlationId = Str::uuid()->toString();

        $this->event = $event->load(['eventRecurrences' => function ($query) {
            $query->orderBy('date', 'asc')->orderBy('time_start', 'asc');
        }, 'eventCategory:id,name', 'organization:id,name', 'locations:locations.id,locations.name']);

        $this->mergedSchedules = $this->mergeEventRecurrences($this->event->eventRecurrences);

        $this->categories = Cache::remember('event_categories_dropdown', 3600, function () {
            return EventCategory::active()->get(['id', 'name']);
        });
        $this->organizations = Cache::remember('organizations_dropdown', 3600, function () {
            return Organization::active()->get(['id', 'name']);
        });
        $this->locations = Cache::remember('locations_dropdown', 3600, function () {
            return Location::active()->get(['id', 'name']);
        });
    }

    /**
     * Merge consecutive event recurrences that span midnight.
     */
    private function mergeEventRecurrences($recurrences): array
    {
        if ($recurrences->isEmpty()) {
            return [];
        }

        $merged = [];
        $count = $recurrences->count();

        for ($i = 0; $i < $count; $i++) {
            $blockStartRecurrence = $recurrences->get($i);

            while (
                ($i + 1) < $count &&
                $recurrences->get($i)->time_end->format('H:i:s') === '23:59:59' &&
                $recurrences->get($i + 1)->time_start->format('H:i:s') === '00:00:00' &&
                $recurrences->get($i + 1)->date->isSameDay($recurrences->get($i)->date->copy()->addDay())
            ) {
                $i++;
            }

            $blockEndRecurrence = $recurrences->get($i);

            $startDateTime = Carbon::parse($blockStartRecurrence->date->format('Y-m-d') . ' ' . $blockStartRecurrence->time_start->format('H:i:s'));
            $endDateTime = Carbon::parse($blockEndRecurrence->date->format('Y-m-d') . ' ' . $blockEndRecurrence->time_end->format('H:i:s'));

            $merged[] = [
                'start' => $startDateTime,
                'end' => $endDateTime,
                'duration' => $startDateTime->diffForHumans($endDateTime, true),
            ];
        }

        return $merged;
    }

    /**
     * Open edit confirmation modal.
     */
    public function confirmEdit(int $id): void
    {
        try {
            $this->authorize('access', 'admin.events.edit');
            $this->dispatch('open-modal', 'update-event-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event edit confirmation attempt', [
                'user_id' => Auth::id(),
                'event_id' => $id,
            ]);
            flash()->error('You are not authorized to edit events.');
        }
    }

    /**
     * Open edit modal for the event.
     */
    public function edit(): void
    {
        try {
            $this->authorize('access', 'admin.events.edit');

            $this->form->setEvent($this->event->id);
            $this->dispatch('close-modal', 'update-event-confirmation');
            $this->dispatch('open-modal', 'event-modal');

            Log::info('Event edit modal opened from show page', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event edit attempt', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
            ]);
            flash()->error('You are not authorized to edit events.');
        }
    }

    /**
     * Save event changes.
     */
    public function save(): void
    {
        try {
            $this->authorize('access', 'admin.events.edit');

            $this->form->update();

            // Refresh data without redirect
            $this->event = $this->event->fresh(['eventRecurrences' => function ($query) {
                $query->orderBy('date', 'asc')->orderBy('time_start', 'asc');
            }, 'eventCategory:id,name', 'organization:id,name', 'locations:locations.id,locations.name']);

            $this->mergedSchedules = $this->mergeEventRecurrences($this->event->eventRecurrences);

            $this->dispatch('close-modal', 'event-modal');

            Log::info('Event updated from show page', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event save attempt from show page', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
            ]);
            flash()->error('You are not authorized to edit events.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Event save failed from show page', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while saving the event. #{$this->correlationId}");
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(): void
    {
        try {
            $this->authorize('access', 'admin.events.destroy');

            $this->form->setEvent($this->event->id);
            $this->dispatch('open-modal', 'delete-event-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event delete confirmation from show page', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
            ]);
            flash()->error('You are not authorized to delete events.');
        }
    }

    /**
     * Delete the event.
     */
    public function delete(): void
    {
        try {
            $this->authorize('access', 'admin.events.destroy');

            $eventName = $this->event->name;
            $this->form->delete();

            Log::info('Event deleted from show page', [
                'user_id' => Auth::id(),
                'event_name' => $eventName,
                'correlation_id' => $this->correlationId,
            ]);

            $this->dispatch('deleteSuccess');
            $this->redirect(route('admin.events.index'));
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event deletion from show page', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
            ]);
            flash()->error('You are not authorized to delete events.');
        } catch (\Exception $e) {
            Log::error('Event deletion failed from show page', [
                'user_id' => Auth::id(),
                'event_id' => $this->event->id,
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while deleting the event. #{$this->correlationId}");
        }
    }

    /**
     * Add a custom schedule entry.
     */
    public function addCustomSchedule(): void
    {
        $this->form->addCustomSchedule();
    }

    /**
     * Remove a custom schedule entry by index.
     */
    public function removeCustomSchedule(int $index): void
    {
        $this->form->removeCustomSchedule($index);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.events.show');

        return view('livewire.admin.pages.event.show');
    }
}
