<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\EventCategoryForm;
use App\Models\EventCategory as ModelsEventCategory;
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
use Livewire\WithPagination;

class EventCategory extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public EventCategoryForm $form;

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $editId = null;
    public ?int $deleteId = null;
    public string $correlationId = '';

    public array $table_heads = ['No', 'Name', 'Slug', 'Color', 'Mass Category', 'Status', 'Actions'];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('access', 'admin.event-categories.index');
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
     * Auto-generate slug when name changes.
     */
    public function generateSlug(): void
    {
        $this->form->slug = str($this->form->name)->slug();
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
     * Open create modal.
     */
    public function create(): void
    {
        try {
            $this->authorize('access', 'admin.event-categories.create');

            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
            $this->dispatch('open-modal', 'category-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event category create attempt', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to create event categories.');
        }
    }

    /**
     * Open edit modal for a category.
     */
    public function edit(int $id): void
    {
        try {
            $this->authorize('access', 'admin.event-categories.edit');

            $category = ModelsEventCategory::findOrFail($id);
            $this->editId = $id;
            $this->form->setCategory($category);
            $this->dispatch('open-modal', 'category-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event category edit attempt', [
                'user_id' => Auth::id(),
                'category_id' => $id,
            ]);
            flash()->error('You are not authorized to edit event categories.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Event category not found for edit', ['category_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Event category not found.');
        } catch (\Exception $e) {
            Log::error('Failed to load event category for edit', [
                'category_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to load event category. Please try again.');
        }
    }

    /**
     * Save category (create or update).
     */
    public function save(): void
    {
        Log::info('Event category save action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'edit_id' => $this->editId,
        ]);

        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.event-categories.edit');

                $this->form->update();
                flash()->success('Event category updated successfully.');

                Log::info('Event category updated successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'category_id' => $this->editId,
                ]);
            } else {
                $this->authorize('access', 'admin.event-categories.create');

                $category = $this->form->store();
                flash()->success('Event category created successfully.');

                Log::info('Event category created successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'category_id' => $category->id,
                ]);
            }

            $this->dispatch('close-modal', 'category-modal');
            $this->editId = null;
            $this->deleteId = null;
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event category save attempt', [
                'user_id' => Auth::id(),
                'action' => $this->editId ? 'edit' : 'create',
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Event category save failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while saving the event category. Please try again later. #{$this->correlationId}");
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        try {
            $this->authorize('access', 'admin.event-categories.destroy');

            $category = ModelsEventCategory::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setCategory($category);
            $this->dispatch('open-modal', 'delete-category-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event category delete attempt', [
                'user_id' => Auth::id(),
                'category_id' => $id,
            ]);
            flash()->error('You are not authorized to delete event categories.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Event category not found for delete', ['category_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Event category not found.');
        }
    }

    /**
     * Delete an event category.
     */
    public function delete(): void
    {
        Log::info('Event category deletion action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'category_id' => $this->deleteId,
        ]);

        try {
            $this->authorize('access', 'admin.event-categories.destroy');

            if ($this->deleteId) {
                $categoryName = $this->form->name;
                $this->form->delete();
                flash()->success('Event category deleted successfully.');

                Log::info('Event category deleted successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'deleted_category_name' => $categoryName,
                ]);
            }
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized event category deletion', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to delete event categories.');
        } catch (\Exception $e) {
            Log::error('Event category deletion failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while deleting the event category. Please try again later. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-category-confirmation');
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.event-categories.index');

        $categories = ModelsEventCategory::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('slug', 'like', '%' . $this->search . '%');
        })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.event-category', [
            'categories' => $categories,
            'table_heads' => $this->table_heads,
        ]);
    }
}
