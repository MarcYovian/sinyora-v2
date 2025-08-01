<?php

namespace App\Livewire\Admin\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Livewire\Forms\EventForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public EventForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;
    public $approveId = null;
    public $rejectId = null;

    public $categories;
    public $organizations;
    public $locations;

    public function mount()
    {
        $this->authorize('access', 'admin.events.index');

        $this->categories = EventCategory::active()->get(['id', 'name']);
        $this->organizations = Organization::active()->get(['id', 'name']);
        $this->locations = Location::active()->get(['id', 'name']);
    }

    public function create()
    {
        $this->authorize('access', 'admin.events.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'event-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.events.edit');

        $this->editId = $id;
        $event = Event::find($id);
        $this->form->setEvent($event);
        $this->dispatch('open-modal', 'event-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.events.edit');

            $this->form->update();
            $this->editId = null;
            $this->dispatch('updateSuccess');
        } else {
            $this->authorize('access', 'admin.events.create');

            $this->form->store();
            $this->dispatch('createSuccess');
        }
        $this->dispatch('close-modal', 'event-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.events.destroy');

        $this->deleteId = $id;
        $event = Event::find($id);
        $this->form->setEvent($event);
        $this->dispatch('open-modal', 'delete-event-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.events.destroy');

        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            $this->dispatch('deleteSuccess');
        }
        $this->dispatch('close-modal', 'delete-event-confirmation');
    }

    public function confirmApprove($id)
    {
        $this->authorize('access', 'admin.events.approve');

        $this->approveId = $id;
        $event = Event::find($id);
        $this->form->setEvent($event);
        $this->dispatch('open-modal', 'approve-event-confirmation');
    }

    public function approve()
    {
        $this->authorize('access', 'admin.events.approve');

        if ($this->approveId) {
            $this->form->approve();
            $this->approveId = null;
            if ($this->getErrorBag());
            $this->dispatch('approveSuccess');
        }
        $this->dispatch('close-modal', 'approve-event-confirmation');
    }

    public function confirmReject($id)
    {
        $this->authorize('access', 'admin.events.reject');

        $this->rejectId = $id;
        $event = Event::find($id);
        $this->form->setEvent($event);
        $this->dispatch('open-modal', 'reject-event-confirmation');
    }

    public function reject()
    {
        $this->authorize('access', 'admin.events.reject');

        if ($this->rejectId) {
            $this->form->reject();
            $this->rejectId = null;
            $this->dispatch('rejectSuccess');
        }
        $this->dispatch('close-modal', 'reject-event-confirmation');
    }

    public function render()
    {
        $this->authorize('access', 'admin.events.index');

        // table heads
        $table_heads = ['No', 'Name', 'Recurrence Start', 'Recurrence End', 'Organization', 'Event Category', 'Locations', 'Recurrence Type', 'Status', 'Action'];

        // events list
        $events = Event::with(['eventCategory', 'organization', 'locations', 'eventRecurrences'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('eventCategory', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('organization', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('locations', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('eventRecurrences', function ($query) {
                        $query->where('date', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('status', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.event.index', [
            'table_heads' => $table_heads,
            'events' => $events
        ]);
    }
}
