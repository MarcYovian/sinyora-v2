<?php

namespace App\Livewire\Forms;

use App\Models\EventCategory;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EventCategoryForm extends Form
{
    public ?EventCategory $category;

    #[Validate('required')]
    public string $name = '';

    #[Validate('required|alpha_dash')]
    public string $slug = '';

    #[Validate('required')]
    public string $color = '';

    #[Validate('required|boolean')]
    public bool $is_active = false;

    #[Validate('boolean')]
    public bool $is_mass_category = false;

    public function setCategory(?EventCategory $category)
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->slug = $category->slug ?? '';
        $this->color = $category->color;
        $this->is_active = $category->is_active;
        $this->is_mass_category = $category->is_mass_category ?? false;
    }

    public function store()
    {
        $this->validate();

        EventCategory::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'is_mass_category' => $this->is_mass_category,
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->category->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'is_mass_category' => $this->is_mass_category,
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

    /**
     * Generate slug from name
     */
    public function generateSlug()
    {
        $this->slug = Str::slug($this->name);
    }
}
