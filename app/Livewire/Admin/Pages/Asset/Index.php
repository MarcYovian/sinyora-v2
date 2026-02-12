<?php

namespace App\Livewire\Admin\Pages\Asset;

use App\Livewire\Forms\AssetForm;
use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithFileUploads, AuthorizesRequests;

    #[Layout('layouts.app')]

    public AssetForm $form;

    public $categories;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 's')]
    public string $filterStatus = '';

    public ?int $editId = null;
    public ?int $deleteId = null;
    public string $correlationId = '';

    public array $table_heads = ['No', 'Image', 'Name', 'Code', 'Quantity', 'Storage', 'Status', 'Actions'];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('access', 'admin.assets.index');
        $this->categories = Cache::remember('asset_categories_dropdown', 3600, function () {
            return AssetCategory::all(['id', 'name']);
        });
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
     * Reset pagination when filterStatus changes.
     */
    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Auto-generate slug when name changes.
     */
    public function updatedFormName(): void
    {
        $this->form->slug = str($this->form->name)->slug();
    }

    /**
     * Log image upload for debugging.
     */
    public function updatedFormImage(): void
    {
        if ($this->form->image) {
            Log::debug('Asset image upload initiated', [
                'originalName' => $this->form->image->getClientOriginalName(),
                'size' => $this->form->image->getSize(),
                'mime' => $this->form->image->getMimeType(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Reset all filters and pagination.
     */
    public function resetFilters(): void
    {
        $this->reset('search', 'filterStatus');
        $this->resetPage();
    }

    /**
     * Open create modal.
     */
    public function create(): void
    {
        try {
            $this->authorize('access', 'admin.assets.create');

            $this->form->resetForm();
            $this->editId = null;
            $this->deleteId = null;
            $this->dispatch('open-modal', 'asset-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset create attempt', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to create assets.');
        }
    }

    /**
     * Open edit modal for an asset.
     */
    public function edit(int $id): void
    {
        try {
            $this->authorize('access', 'admin.assets.edit');

            $asset = Asset::findOrFail($id);
            $this->editId = $id;
            $this->form->setAsset($asset);
            $this->dispatch('open-modal', 'asset-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset edit attempt', [
                'user_id' => Auth::id(),
                'asset_id' => $id,
            ]);
            flash()->error('You are not authorized to edit assets.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Asset not found for edit', ['asset_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Asset not found.');
        } catch (\Exception $e) {
            Log::error('Failed to load asset for edit', [
                'asset_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to load asset. Please try again.');
        }
    }

    /**
     * Save asset (create or update).
     */
    public function save(): void
    {
        Log::info('Asset save action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'edit_id' => $this->editId,
        ]);

        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.assets.edit');

                $this->form->update();
                flash()->success('Asset updated successfully.');

                Log::info('Asset updated successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'asset_id' => $this->editId,
                ]);
            } else {
                $this->authorize('access', 'admin.assets.create');

                $asset = $this->form->store();
                flash()->success('Asset created successfully.');

                Log::info('Asset created successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'asset_id' => $asset->id,
                ]);
            }

            $this->dispatch('close-modal', 'asset-modal');
            $this->editId = null;
            $this->deleteId = null;
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset save attempt', [
                'user_id' => Auth::id(),
                'action' => $this->editId ? 'edit' : 'create',
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Asset save failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while saving the asset. Please try again later. #{$this->correlationId}");
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        try {
            $this->authorize('access', 'admin.assets.destroy');

            $asset = Asset::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setAsset($asset);
            $this->dispatch('open-modal', 'delete-asset-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset delete attempt', [
                'user_id' => Auth::id(),
                'asset_id' => $id,
            ]);
            flash()->error('You are not authorized to delete assets.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Asset not found for delete', ['asset_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Asset not found.');
        }
    }

    /**
     * Delete an asset.
     */
    public function delete(): void
    {
        Log::info('Asset deletion action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'asset_id' => $this->deleteId,
        ]);

        try {
            $this->authorize('access', 'admin.assets.destroy');

            if ($this->deleteId) {
                $assetName = $this->form->name;
                $this->form->delete();
                flash()->success('Asset deleted successfully.');

                Log::info('Asset deleted successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'deleted_asset_name' => $assetName,
                ]);
            }
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized asset deletion', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to delete assets.');
        } catch (\Exception $e) {
            Log::error('Asset deletion failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while deleting the asset. Please try again later. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-asset-confirmation');
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    /**
     * Remove image from asset.
     */
    public function removeImage(): void
    {
        try {
            $this->form->removeImage();
            Log::info('Asset image removed', ['user_id' => Auth::id()]);
        } catch (\Exception $e) {
            Log::error('Failed to remove asset image', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to remove image.');
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.assets.index');

        $assets = Asset::query()
            ->select(['id', 'name', 'slug', 'code', 'quantity', 'storage_location', 'is_active', 'asset_category_id', 'image', 'created_at'])
            ->with('assetCategory:id,name')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%')
                        ->orWhere('storage_location', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('is_active', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.asset.index', [
            'table_heads' => $this->table_heads,
            'assets' => $assets,
        ]);
    }
}
