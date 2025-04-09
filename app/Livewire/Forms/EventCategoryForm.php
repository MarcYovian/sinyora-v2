<?php

namespace App\Livewire\Forms;

use App\Models\EventCategory;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EventCategoryForm extends Form
{
    public ?EventCategory $category;

    #[Validate('required')]
    public string $name = '';
    #[Validate('required')]
    public string $color = '';
    #[Validate('required|boolean')]
    public bool $is_active = false;

    public function setCategory(?EventCategory $category)
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->color = $category->color;
        $this->is_active = $category->is_active;
    }

    public function store()
    {
        $this->validate();

        EventCategory::create([
            'name' => $this->name,
            'color' => $this->color,
            'is_active' => $this->is_active,
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->category->update([
            'name' => $this->name,
            'color' => $this->color,
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
