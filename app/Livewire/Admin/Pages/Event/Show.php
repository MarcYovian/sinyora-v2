<?php

namespace App\Livewire\Admin\Pages\Event;

use App\Livewire\Forms\EventForm;
use App\Livewire\Forms\EventShowForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    #[Layout('layouts.app')]

    public EventForm $form;
    public Event $event;
    public $categories;
    public $locations;
    public $organizations;
    public array $mergedSchedules = [];

    public function mount(Event $event)
    {
        $this->authorize('access', 'admin.events.show');

        $this->event = $event->load(['eventRecurrences' => function ($query) {
            $query->orderBy('date', 'asc')->orderBy('time_start', 'asc');
        }]);

        $this->mergedSchedules = $this->mergeEventRecurrences($this->event->eventRecurrences);

        $this->categories = EventCategory::active()->get(['id', 'name']);
        $this->organizations = Organization::active()->get(['id', 'name']);
        $this->locations = Location::active()->get(['id', 'name']);
    }

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

    public function confirmEdit($id)
    {
        $this->authorize('access', 'admin.events.edit');

        $this->dispatch('open-modal', 'update-event-confirmation');
    }

    public function edit()
    {
        $this->authorize('access', 'admin.events.edit');

        $this->form->setEvent($this->event->id);

        $this->dispatch('close-modal', 'update-event-confirmation');
        $this->dispatch('open-modal', 'event-modal');
    }

    public function save()
    {
        $this->authorize('access', 'admin.events.edit');

        $this->form->update();

        // Update data tanpa redirect
        $this->event = $this->event->fresh();

        $this->dispatch('close-modal', 'event-modal');
    }

    public function confirmDelete()
    {
        $this->authorize('access', 'admin.events.destroy');

        $this->form->setEvent($this->event->id);
        $this->dispatch('open-modal', 'delete-event-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.events.destroy');

        $this->form->delete();
        $this->dispatch('deleteSuccess');
        $this->redirect(route('admin.events.index'));
    }

    public function addCustomSchedule()
    {
        $this->form->addCustomSchedule();
    }

    // Metode BARU untuk di-trigger dari view
    public function removeCustomSchedule($index)
    {
        $this->form->removeCustomSchedule($index);
    }

    public function render()
    {
        $this->authorize('access', 'admin.events.show');

        return view('livewire.admin.pages.event.show');
    }
}
