<?php

namespace App\Livewire\Forms;

use App\Models\EventCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EventCategoryForm extends Form
{
    public ?EventCategory $category = null;

    public string $name = '';
    public string $slug = '';
    public string $color = '#000000';
    public $is_active = 1;
    public $is_mass_category = 0;

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('event_categories')->ignore($this->category?->id)],
            'color' => ['required', 'string', 'max:7'],
            'is_active' => ['required', 'boolean'],
            'is_mass_category' => ['boolean'],
        ];
    }

    /**
     * Set the category instance for editing.
     */
    public function setCategory(?EventCategory $category): void
    {
        $this->category = $category;

        if ($category) {
            $this->name = $category->name ?? '';
            $this->slug = $category->slug ?? '';
            $this->color = $category->color ?? '#000000';
            $this->is_active = $category->is_active ? 1 : 0;
            $this->is_mass_category = $category->is_mass_category ? 1 : 0;
        }
    }

    /**
     * Create a new event category.
     */
    public function store(): EventCategory
    {
        $validated = $this->validate();

        try {
            $category = EventCategory::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'color' => $validated['color'],
                'is_active' => $validated['is_active'],
                'is_mass_category' => $validated['is_mass_category'] ?? false,
            ]);

            Log::info('Event category created via form', [
                'category_id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'created_by' => Auth::id(),
            ]);

            $this->resetForm();

            return $category;
        } catch (\Exception $e) {
            Log::error('Failed to create event category', [
                'name' => $this->name,
                'slug' => $this->slug,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing event category.
     */
    public function update(): EventCategory
    {
        $validated = $this->validate();

        if (!$this->category) {
            throw new \RuntimeException('No event category set for update');
        }

        try {
            $this->category->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'color' => $validated['color'],
                'is_active' => $validated['is_active'],
                'is_mass_category' => $validated['is_mass_category'] ?? false,
            ]);

            Log::info('Event category updated via form', [
                'category_id' => $this->category->id,
                'name' => $this->category->name,
                'updated_by' => Auth::id(),
            ]);

            $updatedCategory = $this->category;
            $this->resetForm();

            return $updatedCategory;
        } catch (\Exception $e) {
            Log::error('Failed to update event category', [
                'category_id' => $this->category->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete the event category.
     */
    public function delete(): bool
    {
        if (!$this->category) {
            Log::warning('Delete attempt with no event category set', ['user_id' => Auth::id()]);
            return false;
        }

        try {
            $categoryId = $this->category->id;
            $categoryName = $this->category->name;

            $this->category->delete();

            Log::info('Event category deleted via form', [
                'deleted_category_id' => $categoryId,
                'deleted_category_name' => $categoryName,
                'deleted_by' => Auth::id(),
            ]);

            $this->resetForm();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete event category', [
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
        $this->reset(['name', 'slug', 'color', 'is_active', 'is_mass_category']);
        $this->category = null;
        $this->color = '#000000';
        $this->is_active = 1;
        $this->is_mass_category = 0;
        $this->resetErrorBag();
    }
}
