<?php

namespace App\Livewire\Admin\Pages\Event;

use App\Livewire\Forms\EventShowForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    #[Layout('layouts.app')]

    public EventShowForm $form;
    public Event $event;
    public $categories;
    public $locations;
    public $organizations;

    public function mount(Event $event)
    {
        $this->authorize('access', 'admin.events.show');

        $this->event = $event;

        $this->categories = EventCategory::active()->get(['id', 'name']);
        $this->organizations = Organization::active()->get(['id', 'name']);
        $this->locations = Location::active()->get(['id', 'name']);
    }

    public function confirmEdit($id)
    {
        $this->authorize('access', 'admin.events.edit');

        $this->form->setEvent($this->event);
        $this->dispatch('open-modal', 'update-event-confirmation');
    }

    public function edit()
    {
        $this->authorize('access', 'admin.events.edit');

        $this->dispatch('close-modal', 'update-event-confirmation');
        $this->dispatch('open-modal', 'event-modal');
    }

    public function save()
    {
        $this->authorize('access', 'admin.events.edit');

        $this->form->update();

        // Update data tanpa redirect
        $this->event = $this->event->fresh();
        $this->form->setEvent($this->event);

        $this->dispatch('close-modal', 'event-modal');
    }

    public function confirmDelete()
    {
        $this->authorize('access', 'admin.events.destroy');

        $this->form->setEvent($this->event);
        $this->dispatch('open-modal', 'delete-event-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.events.destroy');

        $this->form->delete();
        $this->dispatch('deleteSuccess');
        $this->redirect(route('admin.events.index'));
    }

    public function render()
    {
        $this->authorize('access', 'admin.events.show');

        return view('livewire.admin.pages.event.show');
    }
}
