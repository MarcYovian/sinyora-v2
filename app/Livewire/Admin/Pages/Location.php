<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\LocationForm;
use App\Models\Location as ModelsLocation;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Location extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    public LocationForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function create()
    {
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'location-modal');
    }

    public function edit($id)
    {
        $this->editId = $id;
        $location = ModelsLocation::find($id);
        $this->form->setLocation($location);
        $this->dispatch('open-modal', 'location-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->form->update();
            $this->editId = null;
            $this->dispatch('updateSuccess');
        } else {
            $this->form->store();
            $this->dispatch('createSuccess');
        }
        $this->dispatch('close-modal', 'location-modal');
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $location = ModelsLocation::find($id);
        $this->form->setLocation($location);
        $this->dispatch('open-modal', 'delete-location-confirmation');
    }

    public function delete()
    {
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
