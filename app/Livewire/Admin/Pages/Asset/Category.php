<?php

namespace App\Livewire\Admin\Pages\Asset;

use App\Livewire\Forms\AssetCategoryForm;
use App\Models\AssetCategory;
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

class Category extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public AssetCategoryForm $form;

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $editId = null;
    public ?int $deleteId = null;
    public string $correlationId = '';

    public array $table_heads = ['No', 'Name', 'Slug', 'Status', 'Actions'];

    /**
     * Auto-generate slug when name changes.
     */
    public function updatedFormName(): void
    {
        $this->form->slug = str($this->form->name)->slug();
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
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
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('access', 'admin.asset-categories.index');
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Open create modal.
     */
    public function create(): void
    {
        try {
            $this->authorize('access', 'admin.asset-categories.create');

            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
            $this->dispatch('open-modal', 'category-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset category create attempt', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to create categories.');
        }
    }

    /**
     * Open edit modal for a category.
     */
    public function edit(int $id): void
    {
        try {
            $this->authorize('access', 'admin.asset-categories.edit');

            $category = AssetCategory::findOrFail($id);
            $this->editId = $id;
            $this->form->setCategory($category);
            $this->dispatch('open-modal', 'category-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset category edit attempt', [
                'user_id' => Auth::id(),
                'category_id' => $id,
            ]);
            flash()->error('You are not authorized to edit categories.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Asset category not found for edit', ['category_id' => $id]);
            flash()->error('Category not found.');
        } catch (\Exception $e) {
            Log::error('Failed to load asset category for edit', [
                'category_id' => $id,
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to load category. Please try again.');
        }
    }

    /**
     * Save category (create or update).
     */
    public function save(): void
    {
        Log::info('Asset category save action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'edit_id' => $this->editId,
        ]);

        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.asset-categories.edit');

                $this->form->update();
                flash()->success('Category updated successfully.');

                Log::info('Asset category updated successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'category_id' => $this->editId,
                ]);
            } else {
                $this->authorize('access', 'admin.asset-categories.create');

                $category = $this->form->store();
                flash()->success('Category created successfully.');

                Log::info('Asset category created successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'category_id' => $category->id,
                ]);
            }

            $this->dispatch('close-modal', 'category-modal');
            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset category save attempt', [
                'user_id' => Auth::id(),
                'action' => $this->editId ? 'edit' : 'create',
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (ValidationException $e) {
            $this->setErrorBag($e->errors());
        } catch (\Exception $e) {
            Log::error('Asset category save failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while saving the category. Please try again later. #{$this->correlationId}");
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        try {
            $this->authorize('access', 'admin.asset-categories.destroy');

            $category = AssetCategory::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setCategory($category);
            $this->dispatch('open-modal', 'delete-category-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset category delete attempt', [
                'user_id' => Auth::id(),
                'category_id' => $id,
            ]);
            flash()->error('You are not authorized to delete categories.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Asset category not found for delete', ['category_id' => $id]);
            flash()->error('Category not found.');
        }
    }

    /**
     * Delete a category.
     */
    public function delete(): void
    {
        Log::info('Asset category deletion action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'category_id' => $this->deleteId,
        ]);

        try {
            $this->authorize('access', 'admin.asset-categories.destroy');

            $this->form->delete();
            flash()->success('Category deleted successfully.');

            Log::info('Asset category deleted successfully', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'deleted_category_id' => $this->deleteId,
            ]);
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset category deletion', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to delete categories.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                Log::warning('Asset category deletion failed due to foreign key constraint', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'category_id' => $this->deleteId,
                ]);
                flash()->error('Tidak dapat menghapus kategori ini karena masih digunakan oleh satu atau lebih aset.');
            } else {
                Log::error('Asset category deletion failed (QueryException)', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'error' => $e->getMessage(),
                ]);
                flash()->error("Terjadi kesalahan database saat menghapus kategori. #{$this->correlationId}");
            }
        } catch (\Exception $e) {
            Log::error('Asset category deletion failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while deleting the category. Please try again later. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-category-confirmation');
            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.asset-categories.index');

        $categories = AssetCategory::query()
            ->select(['id', 'name', 'slug', 'is_active', 'created_at'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })->latest()->paginate(10);

        return view('livewire.admin.pages.asset.category', [
            'categories' => $categories,
            'table_heads' => $this->table_heads,
        ]);
    }
}
