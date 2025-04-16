<?php

namespace App\Livewire\Forms;

use App\Models\AssetCategory;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AssetCategoryForm extends Form
{
    public ?AssetCategory $category;

    #[Validate('required')]
    public string $name = '';
    #[Validate('required')]
    public bool $is_active = false;

    public function setCategory(?AssetCategory $category)
    {
        $this->category = $category;
        if ($category) {
            $this->name = $category->name;
            $this->is_active = $category->is_active;
        }
    }

    public function store()
    {
        $this->validate();

        $slugName = Str::slug($this->name . ' ' . now()->timestamp);

        AssetCategory::create([
            'name' => $this->name,
            'slug' => $slugName,
            'is_active' => $this->is_active,
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $slugName = Str::slug($this->name . ' ' . now()->timestamp);

        $this->category->update([
            'name' => $this->name,
            'slug' => $slugName,
            'is_active' => $this->is_active,
        ]);

        $this->reset();
    }
    public function delete()
    {
        if ($this->category) {
            $this->category->delete();
            $this->reset();
        }
    }
}
