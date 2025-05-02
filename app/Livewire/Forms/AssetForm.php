<?php

namespace App\Livewire\Forms;

use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    public string $description = '';
    public int $quantity = 0;
    public string $storage_location = '';
    public $is_active = 0;
    public ?string $existingImage = null;

    public function rules()
    {
        return [
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('assets')->ignore($this->asset?->id)],
            'code' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'quantity' => ['required', 'integer'],
            'storage_location' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'image' => [
                'nullable',
                'image',
                'max:2048', // 2MB max
                'mimes:jpg,jpeg,png,gif',
            ]
        ];
    }


    public function setAsset(?Asset $asset)
    {
        $this->asset = $asset;
        if ($asset) {
            $this->asset_category_id = $asset->asset_category_id;
            $this->name = $asset->name;
            $this->slug = $asset->slug;
            $this->code = $asset->code;
            $this->description = $asset->description;
            $this->quantity = $asset->quantity;
            $this->storage_location = $asset->storage_location;
            $this->is_active = $asset->is_active;
            $this->existingImage = $asset->image;
        }
    }

    public function store()
    {
        $this->validate();

        $imagePath = $this->storeImage();

        Asset::create([
            'asset_category_id' => $this->asset_category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'storage_location' => $this->storage_location,
            'is_active' => $this->is_active,
            'image' => $imagePath,
            'created_by' => Auth::id(),
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $imagePath = $this->storeImage() ?? $this->existingImage;

        $this->asset->update([
            'asset_category_id' => $this->asset_category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'storage_location' => $this->storage_location,
            'is_active' => $this->is_active,
            'image' => $imagePath,
            'created_by' => Auth::id(),
        ]);

        $this->reset();
    }
    public function delete()
    {
        if ($this->asset) {
            $this->removeImage();
            $this->asset->delete();
            $this->reset();
        }
    }

    protected function storeImage(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Delete old image if exists
        if ($this->existingImage) {
            Storage::disk('public')->delete($this->existingImage);
        }

        // Store new image
        return $this->image->store('assets', 'public');
    }

    public function resetForm(): void
    {
        $this->resetExcept('existingImage');
        $this->asset = null;
        $this->existingImage = null;
        $this->resetErrorBag();
    }

    public function removeImage(): void
    {
        if ($this->existingImage) {
            Storage::disk('public')->delete($this->existingImage);
            $this->existingImage = null;
        }
        $this->image = null;
    }
}
