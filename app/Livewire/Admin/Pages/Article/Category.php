<?php

namespace App\Livewire\Admin\Pages\Article;

use App\Livewire\Forms\ArticleCategoryForm;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Category extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public ArticleCategoryForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function create()
    {
        $this->authorize('access', 'admin.articles.categories.create');
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'category-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.articles.categories.edit');
        $this->editId = $id;
        $category = ArticleCategory::find($id);
        $this->form->setCategory($category);
        $this->dispatch('open-modal', 'category-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.articles.categories.edit');
            $this->form->update();
            $this->editId = null;
            toastr()->success('Category updated successfully');
        } else {
            $this->authorize('access', 'admin.articles.categories.create');
            $this->form->store();
            toastr()->success('Category created successfully');
        }
        $this->dispatch('close-modal', 'category-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.articles.categories.delete');
        $this->deleteId = $id;
        $category = ArticleCategory::find($id);
        $this->form->setCategory($category);
        $this->dispatch('open-modal', 'delete-category-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.articles.categories.delete');
        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            toastr()->success('Category deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-category-confirmation');
    }

    public function render()
    {
        $this->authorize('access', 'admin.articles.categories.index');

        $table_heads = ['#', 'Name', 'Actions'];

        $categories = ArticleCategory::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);
        return view('livewire.admin.pages.article.category', [
            'categories' => $categories,
            'table_heads' => $table_heads,
        ]);
    }
}
