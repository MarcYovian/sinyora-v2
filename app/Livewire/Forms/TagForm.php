<?php

namespace App\Livewire\Forms;

use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TagForm extends Form
{
    public ?Tag $tag = null;

    public string $name = '';

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('article_categories', 'name')->ignore($this->tag?->id)],
        ];
    }

    public function setTag(?Tag $tag)
    {
        $this->tag = $tag;
        if ($tag) {
            $this->name = $tag->name;
        }
    }

    public function store()
    {
        $this->validate();

        $slugName = Str::slug($this->name . ' ' . now()->timestamp);

        Tag::create([
            'name' => $this->name,
            'slug' => $slugName,
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $slugName = Str::slug($this->name . ' ' . now()->timestamp);

        $this->tag->update([
            'name' => $this->name,
            'slug' => $slugName,
        ]);

        $this->reset();
    }
    public function delete()
    {
        if ($this->tag) {
            $this->tag->delete();
            $this->reset();
        }
    }
}
