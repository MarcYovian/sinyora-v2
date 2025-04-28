<?php

namespace App\Livewire\Forms;

use App\Models\ArticleCategory;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ArticleCategoryForm extends Form
{
    public ?ArticleCategory $category = null;

    public string $name = '';
    // #[Validate('required')]
    // public bool $is_active = false;

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('article_categories', 'name')->ignore($this->category?->id)],
            // 'is_active' => ['required', 'boolean'],
        ];
    }

    public function setCategory(?ArticleCategory $category)
    {
        $this->category = $category;
        if ($category) {
            $this->name = $category->name;
            // $this->is_active = $category->is_active;
        }
    }

    public function store()
    {
        $this->validate();

        $slugName = Str::slug($this->name . ' ' . now()->timestamp);

        ArticleCategory::create([
            'name' => $this->name,
            'slug' => $slugName,
            // 'is_active' => $this->is_active,
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
            // 'is_active' => $this->is_active,
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
