<?php

namespace App\Livewire\Admin\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Livewire\Forms\EventForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public EventForm $form;
    public $editId = null;
    public $deleteId = null;
    public $approveId = null;
    public $rejectId = null;

    public $categories;
    public $organizations;
    public $locations;

    #[Url(keep: true)]
    public string $search = '';

    #[Url(as: 'status', keep: true)]
    public string $filterStatus = '';

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
        $this->form->setEvent($id);
        $this->dispatch('open-modal', 'event-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.events.edit');

            $this->form->update();
            $this->editId = null;
            toastr()->success(__('Event updated successfully.'));
        } else {
            $this->authorize('access', 'admin.events.create');

            $this->form->store();
            toastr()->success(__('Event created successfully.'));
        }
        $this->dispatch('close-modal', 'event-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.events.destroy');

        $this->deleteId = $id;
        $this->form->setEvent($id);
        $this->dispatch('open-modal', 'delete-event-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.events.destroy');

        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            toastr()->success(__('Event deleted successfully.'));
        }
        $this->dispatch('close-modal', 'delete-event-confirmation');
    }

    public function confirmApprove($id)
    {
        $this->authorize('access', 'admin.events.approve');
        $this->form->resetErrorBag();
        $this->approveId = $id;
        $this->form->setEvent($id);
        $this->dispatch('open-modal', 'approve-event-confirmation');
    }

    public function approve()
    {
        $this->authorize('access', 'admin.events.approve');

        if (!$this->approveId) {
            return;
        }
        try {
            $this->form->approve();
            $this->approveId = null;
            toastr()->success('Event approved successfully.');
            $this->dispatch('close-modal', 'approve-event-confirmation');
        } catch (ValidationException $e) {
            toastr()->error($e->validator->errors()->first());
        } catch (\Exception $e) {
            // 4. Tangkap error umum lainnya
            toastr()->error('Terjadi kesalahan yang tidak terduga.');
            Log::error('Caught Approval Exception in Component: ' . $e->getMessage());
        }
    }

    public function confirmReject($id)
    {
        $this->authorize('access', 'admin.events.reject');

        $this->rejectId = $id;
        $this->form->setEvent($id);
        $this->dispatch('open-modal', 'reject-event-confirmation');
    }

    public function reject()
    {
        $this->authorize('access', 'admin.events.reject');

        if (!$this->rejectId) {
            return;
        }

        try {
            $this->form->reject();
            $this->rejectId = null;
            toastr()->success('Event rejected successfully.');
            $this->dispatch('close-modal', 'reject-event-confirmation');
        } catch (ValidationException $e) {
            toastr()->error($e->validator->errors()->first());
        } catch (\Exception $e) {
            // 4. Tangkap error umum lainnya
            toastr()->error('Terjadi kesalahan yang tidak terduga.');
            Log::error('Caught Rejection Exception in Component: ' . $e->getMessage());
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'filterStatus');
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('access', 'admin.events.index');

        // table heads
        $table_heads = ['No', 'Event', 'Period', 'Categories', 'Locations', 'Status', 'Action'];

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
                    });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.event.index', [
            'table_heads' => $table_heads,
            'events' => $events
        ]);
    }
}
