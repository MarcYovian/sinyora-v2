<?php

namespace App\Livewire\Admin\Pages\Article;

use App\Livewire\Forms\TagForm;
use App\Models\Tag as ModelsTag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Tag extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public TagForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function create()
    {
        $this->authorize('access', 'admin.articles.tags.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'tag-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.articles.tags.edit');

        $this->editId = $id;
        $tag = ModelsTag::find($id);
        $this->form->setTag($tag);
        $this->dispatch('open-modal', 'tag-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.articles.tags.edit');

            $this->form->update();
            $this->editId = null;
            toastr()->success('Tag updated successfully');
        } else {
            $this->authorize('access', 'admin.articles.tags.create');

            $this->form->store();
            toastr()->success('Tag created successfully');
        }

        $this->dispatch('close-modal', 'tag-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.articles.tags.destroy');

        $this->deleteId = $id;
        $tag = ModelsTag::find($id);
        $this->form->setTag($tag);
        $this->dispatch('open-modal', 'delete-tag-confirmation');
    }


    public function delete()
    {
        $this->authorize('access', 'admin.articles.tags.destroy');

        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            toastr()->success('Tag deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-tag-confirmation');
    }
    public function render()
    {
        // Authorization
        $this->authorize('access', 'admin.articles.tags.index');

        // Table heads
        $table_heads = ['#', 'Name', 'Actions'];

        // Table data
        $tags = ModelsTag::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);

        return view('livewire.admin.pages.article.tag', [
            'tags' => $tags,
            'table_heads' => $table_heads
        ]);
    }
}
