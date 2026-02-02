<?php

namespace App\Livewire\Admin\Pages\Asset;

use App\Livewire\Forms\AssetCategoryForm;
use App\Models\AssetCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Category extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public AssetCategoryForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function updatedFormName()
    {
        $this->form->slug = str($this->form->name)->slug();
    }

    public function create()
    {
        $this->authorize('access', 'admin.asset-categories.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'category-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.asset-categories.edit');

        $this->editId = $id;
        $category = AssetCategory::find($id);
        $this->form->setCategory($category);
        $this->dispatch('open-modal', 'category-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.asset-categories.edit');

            $this->form->update();
            $this->editId = null;
            flash()->success('Category updated successfully');
        } else {
            $this->authorize('access', 'admin.asset-categories.create');

            $this->form->store();
            flash()->success('Category created successfully');
        }
        $this->dispatch('close-modal', 'category-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.asset-categories.destroy');

        $this->deleteId = $id;
        $category = AssetCategory::find($id);
        $this->form->setCategory($category);
        $this->dispatch('open-modal', 'delete-category-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.asset-categories.destroy');

        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            flash()->success('Category deleted successfully');
        }

        $this->dispatch('close-modal', 'delete-category-confirmation');
    }
    public function render()
    {
        // Authorization
        $this->authorize('access', 'admin.asset-categories.index');

        $table_heads = ['#', 'Name', 'slug', 'Status', 'Actions'];

        $categories = AssetCategory::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('slug', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);

        return view('livewire.admin.pages.asset.category', [
            'categories' => $categories,
            'table_heads' => $table_heads,
        ]);
    }
}
