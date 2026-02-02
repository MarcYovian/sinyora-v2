<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\LocationForm;
use App\Models\Location as ModelsLocation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Location extends Component
{
    use WithPagination, WithFileUploads, AuthorizesRequests;

    #[Layout('layouts.app')]

    public LocationForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;


    public function updatedFormImage()
    {
        Log::info('Upload masuk', [
            'token_match' => request()->session()->token() === request()->header('X-CSRF-TOKEN'),
            'session_exists' => session()->has('_token'),
            'csrf_token' => request()->session()->token(),
            'file_sementara' => [
                'originalName' => $this->form->image->getClientOriginalName(),
                'size' => $this->form->image->getSize(),
                'mime' => $this->form->image->getMimeType(),
            ]
        ]);
    }

    public function updatedActivated()
    {
        $this->form->is_active = $this->activated;
    }

    public function create()
    {
        $this->authorize('access', 'admin.locations.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'location-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.locations.edit');

        $this->editId = $id;
        $location = ModelsLocation::find($id);
        $this->form->setLocation($location);
        $this->dispatch('open-modal', 'location-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.locations.edit');
            $this->form->update();
            $this->editId = null;
            flash()->success('Location updated successfully');
        } else {
            $this->authorize('access', 'admin.locations.create');

            $this->form->store();
            flash()->success('Location created successfully');
        }
        $this->dispatch('close-modal', 'location-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.locations.destroy');

        $this->deleteId = $id;
        $location = ModelsLocation::find($id);
        $this->form->setLocation($location);
        $this->dispatch('open-modal', 'delete-location-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.locations.destroy');

        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            $this->dispatch('deleteSuccess');
        }
        $this->dispatch('close-modal', 'delete-location-confirmation');
    }

    public function removeImage()
    {
        $this->form->removeImage();
    }

    public function render()
    {
        $this->authorize('access', 'admin.locations.index');

        $table_heads = ['#', 'Image', 'Name', 'Description', 'Status', 'Actions'];

        $locations = ModelsLocation::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);

        return view('livewire.admin.pages.location', [
            'locations' => $locations,
            'table_heads' => $table_heads
        ]);
    }
}
