<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\LocationForm;
use App\Models\Location as ModelsLocation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
    public string $search = '';

    public ?int $editId = null;
    public ?int $deleteId = null;
    public string $correlationId = '';

    public array $table_heads = ['No', 'Image', 'Name', 'Description', 'Status', 'Actions'];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Log image upload for debugging.
     */
    public function updatedFormImage(): void
    {
        if ($this->form->image) {
            Log::debug('Location image upload initiated', [
                'originalName' => $this->form->image->getClientOriginalName(),
                'size' => $this->form->image->getSize(),
                'mime' => $this->form->image->getMimeType(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Open create modal.
     */
    public function create(): void
    {
        $this->authorize('access', 'admin.locations.create');

        $this->form->resetForm();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'location-modal');
    }

    /**
     * Open edit modal for a location.
     */
    public function edit(int $id): void
    {
        try {
            $this->authorize('access', 'admin.locations.edit');

            $location = ModelsLocation::findOrFail($id);
            $this->editId = $id;
            $this->form->setLocation($location);
            $this->dispatch('open-modal', 'location-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized location edit attempt', ['location_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('You are not authorized to edit this location.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Location not found for edit', ['location_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Location not found.');
        } catch (\Exception $e) {
            Log::error('Failed to load location for edit', [
                'location_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to load location. Please try again.');
        }
    }

    /**
     * Save location (create or update).
     */
    public function save(): void
    {
        Log::info('Location save action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'edit_id' => $this->editId,
        ]);

        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.locations.edit');

                $this->form->update();
                flash()->success('Location updated successfully');

                Log::info('Location updated successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'location_id' => $this->editId,
                ]);
            } else {
                $this->authorize('access', 'admin.locations.create');

                $this->form->store();
                flash()->success('Location created successfully');

                Log::info('Location created successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                ]);
            }

            $this->dispatch('close-modal', 'location-modal');
            $this->editId = null;
            $this->deleteId = null;
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized location save attempt', [
                'user_id' => Auth::id(),
                'edit_id' => $this->editId,
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Location save failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Failed to save location. Please try again. #{$this->correlationId}");
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        try {
            $this->authorize('access', 'admin.locations.destroy');

            $location = ModelsLocation::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setLocation($location);
            $this->dispatch('open-modal', 'delete-location-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized location delete attempt', ['location_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('You are not authorized to delete this location.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Location not found for delete', ['location_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Location not found.');
        }
    }

    /**
     * Delete a location.
     */
    public function delete(): void
    {
        Log::info('Location deletion action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'location_id' => $this->deleteId,
        ]);

        try {
            $this->authorize('access', 'admin.locations.destroy');

            if ($this->deleteId) {
                $locationName = $this->form->name;
                $this->form->delete();

                flash()->success('Location deleted successfully');
                Log::info('Location deleted successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'deleted_location_name' => $locationName,
                ]);
            }
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized location deletion', [
                'user_id' => Auth::id(),
                'delete_id' => $this->deleteId,
            ]);
            flash()->error('You are not authorized to delete this location.');
        } catch (\Exception $e) {
            Log::error('Location deletion failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Failed to delete location. Please try again. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-location-confirmation');
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    /**
     * Remove image from location.
     */
    public function removeImage(): void
    {
        try {
            $this->form->removeImage();
            Log::info('Location image removed', ['user_id' => Auth::id()]);
        } catch (\Exception $e) {
            Log::error('Failed to remove location image', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to remove image.');
        }
    }

    /**
     * Reset all filters and pagination.
     */
    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.locations.index');

        $locations = ModelsLocation::query()
            ->select(['id', 'name', 'description', 'is_active', 'image', 'created_at'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(5);

        return view('livewire.admin.pages.location', [
            'locations' => $locations,
            'table_heads' => $this->table_heads,
        ]);
    }
}
