<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\LocationForm;
use App\Models\Location as ModelsLocation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Location extends Component
{
    use WithPagination, WithFileUploads, AuthorizesRequests;

    #[Layout('layouts.app')]

    public LocationForm $form;

    #[Url(as: 'q')]
    public $search = '';

    public $editId = null;
    public $deleteId = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFormImage()
    {
        Log::debug('Location image upload initiated', [
            'originalName' => $this->form->image?->getClientOriginalName(),
            'size' => $this->form->image?->getSize(),
            'mime' => $this->form->image?->getMimeType(),
            'user_id' => auth()->id(),
        ]);
    }

    public function create()
    {
        $this->authorize('access', 'admin.locations.create');

        $this->form->resetForm();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'location-modal');
    }

    public function edit($id)
    {
        try {
            $this->authorize('access', 'admin.locations.edit');

            $location = ModelsLocation::findOrFail($id);
            $this->editId = $id;
            $this->form->setLocation($location);
            $this->dispatch('open-modal', 'location-modal');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Location not found for edit', ['location_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Location not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized location edit attempt', ['location_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to edit this location.');
        } catch (\Exception $e) {
            Log::error('Failed to load location for edit', [
                'location_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to load location. Please try again.');
        }
    }

    public function save()
    {
        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.locations.edit');

                $this->form->update();
                $this->editId = null;
                flash()->success('Location updated successfully');
                Log::info('Location updated via Livewire', ['user_id' => auth()->id()]);
            } else {
                $this->authorize('access', 'admin.locations.create');

                $this->form->store();
                flash()->success('Location created successfully');
                Log::info('Location created via Livewire', ['user_id' => auth()->id()]);
            }
            $this->dispatch('close-modal', 'location-modal');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized location save attempt', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (\Exception $e) {
            Log::error('Failed to save location', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to save location. Please try again.');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $this->authorize('access', 'admin.locations.destroy');

            $location = ModelsLocation::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setLocation($location);
            $this->dispatch('open-modal', 'delete-location-confirmation');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Location not found for delete confirmation', ['location_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Location not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized location delete attempt', ['location_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to delete this location.');
        } catch (\Exception $e) {
            Log::error('Failed to prepare location deletion', [
                'location_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to prepare deletion. Please try again.');
        }
    }

    public function delete()
    {
        try {
            $this->authorize('access', 'admin.locations.destroy');

            if ($this->deleteId) {
                $locationName = $this->form->name;
                $this->form->delete();
                $this->deleteId = null;

                flash()->success('Location deleted successfully');
                Log::info('Location deleted via Livewire', [
                    'location_name' => $locationName,
                    'user_id' => auth()->id(),
                ]);
            }
            $this->dispatch('close-modal', 'delete-location-confirmation');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized location delete attempt', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
            ]);
            flash()->error('You are not authorized to delete this location.');
        } catch (\Exception $e) {
            Log::error('Failed to delete location', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to delete location. Please try again.');
        }
    }

    public function removeImage()
    {
        try {
            $this->form->removeImage();
            Log::info('Location image removed', ['user_id' => auth()->id()]);
        } catch (\Exception $e) {
            Log::error('Failed to remove location image', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to remove image.');
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('access', 'admin.locations.index');

        $table_heads = ['No', 'Image', 'Name', 'Description', 'Status', 'Actions'];

        $locations = ModelsLocation::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);

        return view('livewire.admin.pages.location', [
            'locations' => $locations,
            'table_heads' => $table_heads
        ]);
    }
}

