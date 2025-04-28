<?php

namespace App\Livewire\Admin\Pages\Article;

use App\Livewire\Forms\ArticleCategoryForm;
use App\Models\ArticleCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Category extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    public ArticleCategoryForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function create()
    {
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'category-modal');
    }

    public function edit($id)
    {
        $this->editId = $id;
        $category = ArticleCategory::find($id);
        $this->form->setCategory($category);
        $this->dispatch('open-modal', 'category-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->form->update();
            $this->editId = null;
            toastr()->success('Category updated successfully');
        } else {
            $this->form->store();
            toastr()->success('Category created successfully');
        }
        $this->dispatch('close-modal', 'category-modal');
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $category = ArticleCategory::find($id);
        $this->form->setCategory($category);
        $this->dispatch('open-modal', 'delete-category-confirmation');
    }

    public function delete()
    {
        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            toastr()->success('Category deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-category-confirmation');
    }

    public function render()
    {
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
