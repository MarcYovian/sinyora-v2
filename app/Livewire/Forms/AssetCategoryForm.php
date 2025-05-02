<?php

namespace App\Livewire\Forms;

use App\Models\AssetCategory;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AssetCategoryForm extends Form
{
    public ?AssetCategory $category = null;

    public string $name = '';
    public string $slug = '';
    public $is_active = 0;

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('asset_categories')->ignore($this->category?->id)],
            'is_active' => 'boolean',
        ];
    }

    public function setCategory(?AssetCategory $category)
    {
        $this->category = $category;
        if ($category) {
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->is_active = $category->is_active;
        }
    }

    public function store()
    {
        $this->validate();

        AssetCategory::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->category->update([
            'name' => $this->name,
            'slug' => $this->slug,
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
