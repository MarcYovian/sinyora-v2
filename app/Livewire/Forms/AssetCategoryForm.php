<?php

namespace App\Livewire\Forms;

use App\Models\AssetCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Form;

class AssetCategoryForm extends Form
{
    public ?AssetCategory $category = null;

    public string $name = '';
    public string $slug = '';
    public $is_active = 1;

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('asset_categories')->ignore($this->category?->id)],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Set the category instance for editing.
     */
    public function setCategory(?AssetCategory $category): void
    {
        $this->category = $category;

        if ($category) {
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->is_active = $category->is_active ? 1 : 0;
        }
    }

    /**
     * Create a new category.
     */
    public function store(): AssetCategory
    {
        $this->validate();

        try {
            $category = AssetCategory::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'is_active' => $this->is_active,
            ]);

            Log::info('Asset category created via form', [
                'category_id' => $category->id,
                'name' => $category->name,
                'created_by' => auth()->id(),
            ]);

            $this->reset();

            return $category;
        } catch (\Exception $e) {
            Log::error('Failed to create asset category', [
                'name' => $this->name,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing category.
     */
    public function update(): AssetCategory
    {
        $this->validate();

        if (!$this->category) {
            throw new \RuntimeException('No category set for update');
        }

        try {
            $this->category->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'is_active' => $this->is_active,
            ]);

            Log::info('Asset category updated via form', [
                'category_id' => $this->category->id,
                'name' => $this->category->name,
                'updated_by' => auth()->id(),
            ]);

            $updatedCategory = $this->category;
            $this->reset();

            return $updatedCategory;
        } catch (\Exception $e) {
            Log::error('Failed to update asset category', [
                'category_id' => $this->category->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete the category.
     */
    public function delete(): void
    {
        if (!$this->category) {
            throw new \RuntimeException('No category set for deletion');
        }

        try {
            $categoryId = $this->category->id;
            $categoryName = $this->category->name;

            $this->category->delete();

            Log::info('Asset category deleted via form', [
                'deleted_category_id' => $categoryId,
                'deleted_category_name' => $categoryName,
                'deleted_by' => auth()->id(),
            ]);

            $this->reset();
        } catch (\Exception $e) {
            Log::error('Failed to delete asset category', [
                'category_id' => $this->category->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
