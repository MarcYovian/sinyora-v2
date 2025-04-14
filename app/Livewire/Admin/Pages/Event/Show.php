<?php

namespace App\Livewire\Admin\Pages\Event;

use App\Livewire\Forms\EventShowForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    #[Layout('layouts.app')]

    public EventShowForm $form;
    public Event $event;
    public $categories;
    public $locations;
    public $organizations;

    public function mount(Event $event)
    {
        $this->event = $event;

        $this->categories = EventCategory::active()->get(['id', 'name']);
        $this->organizations = Organization::active()->get(['id', 'name']);
        $this->locations = Location::active()->get(['id', 'name']);
    }

    public function confirmEdit($id)
    {
        $this->form->setEvent($this->event);
        $this->dispatch('open-modal', 'update-event-confirmation');
    }

    public function edit()
    {
        $this->dispatch('close-modal', 'update-event-confirmation');
        $this->dispatch('open-modal', 'event-modal');
    }

    public function save()
    {
        $this->form->update();

        // Update data tanpa redirect
        $this->event = $this->event->fresh();
        $this->form->setEvent($this->event);

        $this->dispatch('close-modal', 'event-modal');
    }

    public function confirmDelete()
    {
        $this->form->setEvent($this->event);
        $this->dispatch('open-modal', 'delete-event-confirmation');
    }

    public function delete()
    {
        $this->form->delete();
        $this->dispatch('deleteSuccess');
        $this->redirect(route('admin.events.index'));
    }

    public function render()
    {
        return view('livewire.admin.pages.event.show');
    }
}
