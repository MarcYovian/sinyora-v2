<?php

namespace App\Livewire\Forms;

use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Form;

class TagForm extends Form
{
    public ?Tag $tag = null;

    public string $name = '';
    public string $slug = '';

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('tags', 'name')->ignore($this->tag?->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('tags', 'slug')->ignore($this->tag?->id)],
        ];
    }

    /**
     * Set the tag instance for editing.
     */
    public function setTag(?Tag $tag): void
    {
        $this->tag = $tag;

        if ($tag) {
            $this->name = $tag->name ?? '';
            $this->slug = $tag->slug ?? '';
        }
    }

    /**
     * Create a new tag.
     */
    public function store(): Tag
    {
        $validated = $this->validate();

        try {
            // Auto-generate slug if empty
            $slug = $this->slug ?: Str::slug($this->name);

            // Ensure unique slug
            $originalSlug = $slug;
            $counter = 1;
            while (Tag::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $tag = Tag::create([
                'name' => $validated['name'],
                'slug' => $slug,
            ]);

            Log::info('Tag created via form', [
                'tag_id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'created_by' => Auth::id(),
            ]);

            $this->resetForm();

            return $tag;
        } catch (\Exception $e) {
            Log::error('Failed to create tag', [
                'name' => $this->name,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing tag.
     */
    public function update(): Tag
    {
        $validated = $this->validate();

        if (!$this->tag) {
            throw new \RuntimeException('No tag set for update');
        }

        try {
            // Auto-generate slug if empty
            $slug = $this->slug ?: Str::slug($this->name);

            // Ensure unique slug (excluding current record)
            $originalSlug = $slug;
            $counter = 1;
            while (Tag::where('slug', $slug)->where('id', '!=', $this->tag->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $this->tag->update([
                'name' => $validated['name'],
                'slug' => $slug,
            ]);

            Log::info('Tag updated via form', [
                'tag_id' => $this->tag->id,
                'name' => $this->tag->name,
                'updated_by' => Auth::id(),
            ]);

            $updatedTag = $this->tag;
            $this->resetForm();

            return $updatedTag;
        } catch (\Exception $e) {
            Log::error('Failed to update tag', [
                'tag_id' => $this->tag->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete the tag.
     */
    public function delete(): bool
    {
        if (!$this->tag) {
            Log::warning('Delete attempt with no tag set', ['user_id' => Auth::id()]);
            return false;
        }

        try {
            $tagId = $this->tag->id;
            $tagName = $this->tag->name;

            $this->tag->delete();

            Log::info('Tag deleted via form', [
                'deleted_tag_id' => $tagId,
                'deleted_tag_name' => $tagName,
                'deleted_by' => Auth::id(),
            ]);

            $this->resetForm();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete tag', [
                'tag_id' => $this->tag?->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reset the form to initial state.
     */
    public function resetForm(): void
    {
        $this->reset(['name', 'slug']);
        $this->tag = null;
        $this->resetErrorBag();
    }
}
