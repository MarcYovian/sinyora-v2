<?php

namespace App\Livewire\Forms;

use App\Models\Location;
use Illuminate\Support\Facades\Storage;
use Livewire\Form;
use Livewire\WithFileUploads;

class LocationForm extends Form
{
    use WithFileUploads;

    public ?Location $location = null;
    public $image = null;
    public string $name = '';
    public string $description = '';
    public bool $is_active = false;
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
                'mimes:jpg,jpeg,png,gif',
            ],
        ];
    }

    public function setLocation(?Location $location = null): void
    {
        $this->location = $location;

        if ($location) {
            $this->name = $location->name;
            $this->description = $location->description;
            $this->is_active = $location->is_active;
            $this->existingImage = $location->image;
        }
    }

    public function store(): void
    {
        $validated = $this->validate();

        $imagePath = $this->storeImage();

        Location::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'image' => $imagePath,
            'is_active' => $validated['is_active'],
        ]);

        $this->resetForm();
    }

    public function update(): void
    {
        $validated = $this->validate();

        $imagePath = $this->storeImage() ?? $this->existingImage;

        $this->location->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'image' => $imagePath,
            'is_active' => $validated['is_active'],
        ]);

        $this->resetForm();
    }

    public function delete(): void
    {
        // Delete the associated image file
        if ($this->location->image) {
            Storage::delete('public/locations/' . $this->location->image);
        }

        $this->location->delete();
        $this->resetForm();
    }

    protected function storeImage(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Delete old image if exists
        if ($this->existingImage) {
            Storage::delete('public/locations/' . $this->existingImage);
        }

        // Store new image
        return $this->image->store('locations', 'public');
    }

    public function resetForm(): void
    {
        $this->resetExcept('existingImage');
        $this->location = null;
        $this->existingImage = null;
        $this->resetErrorBag();
    }

    public function removeImage(): void
    {
        if ($this->existingImage) {
            Storage::delete('public/locations/' . $this->existingImage);
            $this->existingImage = null;
        }
        $this->image = null;
    }
}
