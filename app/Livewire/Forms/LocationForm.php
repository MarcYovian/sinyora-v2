<?php

namespace App\Livewire\Forms;

use App\Models\Location;
use App\Services\ImageService;
use Illuminate\Support\Facades\Log;
use Livewire\Form;
use Livewire\WithFileUploads;

class LocationForm extends Form
{
    use WithFileUploads;

    public ?Location $location = null;
    public $image = null;
    public string $name = '';
    public string $description = '';
    public $is_active = 0;
    public ?string $existingImage = null;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'image' => [
                'nullable',
                'image',
                'max:2048', // 2MB max
                'mimes:jpg,jpeg,png,gif,webp',
            ],
        ];
    }

    public function setLocation(?Location $location = null): void
    {
        $this->location = $location;

        if ($location) {
            $this->name = $location->name;
            $this->description = $location->description ?? '';
            $this->is_active = $location->is_active;
            $this->existingImage = $location->image;
        }
    }

    public function store(): Location
    {
        $validated = $this->validate();

        try {
            $imagePath = $this->storeImage();

            $location = Location::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'image' => $imagePath,
                'is_active' => $validated['is_active'],
            ]);

            Log::info('Location created via form', [
                'location_id' => $location->id,
                'location_name' => $location->name,
                'has_image' => !empty($imagePath),
                'user_id' => auth()->id(),
            ]);

            $this->resetForm();

            return $location;
        } catch (\Exception $e) {
            Log::error('Failed to create location', [
                'user_id' => auth()->id(),
                'name' => $this->name,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function update(): Location
    {
        $validated = $this->validate();

        try {
            $imagePath = $this->storeImage() ?? $this->existingImage;

            $this->location->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'image' => $imagePath,
                'is_active' => $validated['is_active'],
            ]);

            Log::info('Location updated via form', [
                'location_id' => $this->location->id,
                'location_name' => $this->location->name,
                'image_changed' => $imagePath !== $this->existingImage,
                'user_id' => auth()->id(),
            ]);

            $location = $this->location;
            $this->resetForm();

            return $location;
        } catch (\Exception $e) {
            Log::error('Failed to update location', [
                'location_id' => $this->location?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function delete(): bool
    {
        if (!$this->location) {
            Log::warning('Delete attempt with no location set', ['user_id' => auth()->id()]);
            return false;
        }

        try {
            $locationId = $this->location->id;
            $locationName = $this->location->name;
            $imagePath = $this->location->image;

            // Delete the location (observer will handle image deletion)
            $this->location->delete();

            Log::info('Location deleted via form', [
                'location_id' => $locationId,
                'location_name' => $locationName,
                'had_image' => !empty($imagePath),
                'user_id' => auth()->id(),
            ]);

            $this->resetForm();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete location', [
                'location_id' => $this->location?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

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
            }

            // Store new optimized image
            $path = $imageService->optimize($this->image, [
                'path' => 'locations',
                'max_width' => 800,
                'quality' => 80,
                'format' => 'webp',
            ]);

            Log::debug('Location image stored via ImageService', [
                'path' => $path,
                'original_name' => $this->image->getClientOriginalName(),
                'user_id' => auth()->id(),
            ]);

            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to store location image', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            // Fallback to simple storage if ImageService fails
            return $this->image->store('locations', 'public');
        }
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'is_active', 'image']);
        $this->location = null;
        $this->existingImage = null;
        $this->resetErrorBag();
    }

    public function removeImage(): void
    {
        try {
            if ($this->existingImage) {
                $imageService = app(ImageService::class);
                $imageService->delete($this->existingImage);

                // Also update the model if it exists
                if ($this->location) {
                    $this->location->update(['image' => null]);
                }

                Log::info('Location existing image removed', [
                    'path' => $this->existingImage,
                    'user_id' => auth()->id(),
                ]);

                $this->existingImage = null;
            }
            $this->image = null;
        } catch (\Exception $e) {
            Log::error('Failed to remove location image', [
                'path' => $this->existingImage,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}

