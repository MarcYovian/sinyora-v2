<?php

namespace App\Livewire\Forms;

use App\Models\Asset;
use App\Services\ImageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\WithFileUploads;

class AssetForm extends Form
{
    use WithFileUploads;

    public ?Asset $asset = null;

    public $image = null;
    public string $asset_category_id = '';
    public string $name = '';
    public string $slug = '';
    public string $code = '';
    public ?string $description = '';
    public int $quantity = 0;
    public string $storage_location = '';
    public $is_active = 1;
    public ?string $existingImage = null;

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('assets')->ignore($this->asset?->id)],
            'code' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'quantity' => ['required', 'integer', 'min:0'],
            'storage_location' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'image' => [
                'nullable',
                'image',
                'max:2048', // 2MB max
                'mimes:jpg,jpeg,png,gif,webp',
            ],
        ];
    }

    /**
     * Set the asset instance for editing.
     */
    public function setAsset(?Asset $asset): void
    {
        $this->asset = $asset;

        if ($asset) {
            $this->asset_category_id = (string) $asset->asset_category_id;
            $this->name = $asset->name ?? '';
            $this->slug = $asset->slug ?? '';
            $this->code = $asset->code ?? '';
            $this->description = $asset->description ?? '';
            $this->quantity = $asset->quantity ?? 0;
            $this->storage_location = $asset->storage_location ?? '';
            $this->is_active = $asset->is_active ? 1 : 0;
            $this->existingImage = $asset->image;
        }
    }

    /**
     * Create a new asset.
     */
    public function store(): Asset
    {
        $validated = $this->validate();

        try {
            $imagePath = $this->storeImage();

            $asset = Asset::create([
                'asset_category_id' => $validated['asset_category_id'],
                'name' => $validated['name'],
                'slug' => $this->slug,
                'code' => $validated['code'],
                'description' => $validated['description'],
                'quantity' => $validated['quantity'],
                'storage_location' => $validated['storage_location'],
                'is_active' => $validated['is_active'],
                'image' => $imagePath,
                'created_by' => Auth::id(),
            ]);

            Log::info('Asset created via form', [
                'asset_id' => $asset->id,
                'name' => $asset->name,
                'code' => $asset->code,
                'has_image' => !empty($imagePath),
                'created_by' => Auth::id(),
            ]);

            $this->resetForm();

            return $asset;
        } catch (\Exception $e) {
            Log::error('Failed to create asset', [
                'name' => $this->name,
                'code' => $this->code,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing asset.
     */
    public function update(): Asset
    {
        $validated = $this->validate();

        if (!$this->asset) {
            throw new \RuntimeException('No asset set for update');
        }

        try {
            $imagePath = $this->storeImage() ?? $this->existingImage;

            $this->asset->update([
                'asset_category_id' => $validated['asset_category_id'],
                'name' => $validated['name'],
                'slug' => $this->slug,
                'code' => $validated['code'],
                'description' => $validated['description'],
                'quantity' => $validated['quantity'],
                'storage_location' => $validated['storage_location'],
                'is_active' => $validated['is_active'],
                'image' => $imagePath,
            ]);

            Log::info('Asset updated via form', [
                'asset_id' => $this->asset->id,
                'name' => $this->asset->name,
                'image_changed' => $imagePath !== $this->existingImage,
                'updated_by' => Auth::id(),
            ]);

            $updatedAsset = $this->asset;
            $this->resetForm();

            return $updatedAsset;
        } catch (\Exception $e) {
            Log::error('Failed to update asset', [
                'asset_id' => $this->asset->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete the asset.
     */
    public function delete(): bool
    {
        if (!$this->asset) {
            Log::warning('Delete attempt with no asset set', ['user_id' => Auth::id()]);
            return false;
        }

        try {
            $assetId = $this->asset->id;
            $assetName = $this->asset->name;
            $imagePath = $this->asset->image;

            // Note: Image cleanup is handled by AssetObserver
            $this->asset->delete();

            Log::info('Asset deleted via form', [
                'deleted_asset_id' => $assetId,
                'deleted_asset_name' => $assetName,
                'had_image' => !empty($imagePath),
                'deleted_by' => Auth::id(),
            ]);

            $this->resetForm();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete asset', [
                'asset_id' => $this->asset?->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Store uploaded image using ImageService and return path.
     */
    protected function storeImage(): ?string
    {
        if (!$this->image) {
            return null;
        }

        try {
            $imageService = app(ImageService::class);

            // Delete old image if exists
            if ($this->existingImage) {
                $imageService->delete($this->existingImage);
                Log::debug('Old asset image deleted via ImageService', [
                    'path' => $this->existingImage,
                    'user_id' => Auth::id(),
                ]);
            }

            // Store new optimized image
            $path = $imageService->optimize($this->image, [
                'path' => 'assets',
                'max_width' => 800,
                'quality' => 80,
                'format' => 'webp',
            ]);

            Log::debug('Asset image stored via ImageService', [
                'path' => $path,
                'original_name' => $this->image->getClientOriginalName(),
                'user_id' => Auth::id(),
            ]);

            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to store asset image via ImageService', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            // Fallback to simple storage if ImageService fails
            return $this->image->store('assets', 'public');
        }
    }

    /**
     * Reset the form to initial state.
     */
    public function resetForm(): void
    {
        $this->reset([
            'image',
            'asset_category_id',
            'name',
            'slug',
            'code',
            'description',
            'quantity',
            'storage_location',
            'is_active',
            'existingImage',
        ]);
        $this->asset = null;
        $this->is_active = 1;
        $this->quantity = 0;
        $this->resetErrorBag();
    }

    /**
     * Remove the asset image using ImageService.
     */
    public function removeImage(): void
    {
        try {
            if ($this->existingImage) {
                $imageService = app(ImageService::class);
                $imageService->delete($this->existingImage);

                // Also update the model if it exists
                if ($this->asset) {
                    $this->asset->update(['image' => null]);
                }

                Log::info('Asset existing image removed via ImageService', [
                    'path' => $this->existingImage,
                    'user_id' => Auth::id(),
                ]);

                $this->existingImage = null;
            }

            $this->image = null;
        } catch (\Exception $e) {
            Log::error('Failed to remove asset image', [
                'path' => $this->existingImage,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
