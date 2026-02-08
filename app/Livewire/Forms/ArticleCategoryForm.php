<?php

namespace App\Livewire\Forms;

use App\Models\ArticleCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ArticleCategoryForm extends Form
{
    public ?ArticleCategory $category = null;

    public string $name = '';
    public string $slug = '';

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('article_categories', 'name')->ignore($this->category?->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('article_categories', 'slug')->ignore($this->category?->id)],
        ];
    }

    /**
     * Set the category instance for editing.
     */
    public function setCategory(?ArticleCategory $category): void
    {
        $this->category = $category;

        if ($category) {
            $this->name = $category->name ?? '';
            $this->slug = $category->slug ?? '';
        }
    }

    /**
     * Create a new article category.
     */
    public function store(): ArticleCategory
    {
        $validated = $this->validate();

        try {
            // Auto-generate slug if empty
            $slug = $this->slug ?: Str::slug($this->name);

            // Ensure unique slug
            $originalSlug = $slug;
            $counter = 1;
            while (ArticleCategory::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $category = ArticleCategory::create([
                'name' => $validated['name'],
                'slug' => $slug,
            ]);

            Log::info('Article category created via form', [
                'category_id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'created_by' => Auth::id(),
            ]);

            $this->resetForm();

            return $category;
        } catch (\Exception $e) {
            Log::error('Failed to create article category', [
                'name' => $this->name,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing article category.
     */
    public function update(): ArticleCategory
    {
        $validated = $this->validate();

        if (!$this->category) {
            throw new \RuntimeException('No article category set for update');
        }

        try {
            // Auto-generate slug if empty
            $slug = $this->slug ?: Str::slug($this->name);

            // Ensure unique slug (excluding current record)
            $originalSlug = $slug;
            $counter = 1;
            while (ArticleCategory::where('slug', $slug)->where('id', '!=', $this->category->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $this->category->update([
                'name' => $validated['name'],
                'slug' => $slug,
            ]);

            Log::info('Article category updated via form', [
                'category_id' => $this->category->id,
                'name' => $this->category->name,
                'updated_by' => Auth::id(),
            ]);

            $updatedCategory = $this->category;
            $this->resetForm();

            return $updatedCategory;
        } catch (\Exception $e) {
            Log::error('Failed to update article category', [
                'category_id' => $this->category->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete the article category.
     */
    public function delete(): bool
    {
        if (!$this->category) {
            Log::warning('Delete attempt with no article category set', ['user_id' => Auth::id()]);
            return false;
        }

        try {
            $categoryId = $this->category->id;
            $categoryName = $this->category->name;

            $this->category->delete();

            Log::info('Article category deleted via form', [
                'deleted_category_id' => $categoryId,
                'deleted_category_name' => $categoryName,
                'deleted_by' => Auth::id(),
            ]);

            $this->resetForm();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete article category', [
                'category_id' => $this->category?->id,
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
        $this->category = null;
        $this->resetErrorBag();
    }
}
