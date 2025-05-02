<?php

namespace App\Livewire\Admin\Pages\Article;

use App\Livewire\Forms\ArticleForm;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Form extends Component
{
    use WithPagination, WithFileUploads, AuthorizesRequests;

    #[Layout('layouts.app')]

    public ArticleForm $form;

    public $editId;
    public $categories;
    public $tags;
    public $previewContent = '';

    public function mount($id = null)
    {
        $this->authorize('access', 'admin.articles.create');

        if ($id) {
            $this->authorize('access', 'admin.articles.edit');

            $article = Article::find($id);
            $this->form->setArticle($article);
            $this->editId = $article->id;
        }

        $this->categories = ArticleCategory::all();
        $this->tags = Tag::all();
    }

    public function updatedFormTitle($title)
    {
        // Generate slug dari title
        $slug = Str::slug($title);

        // Update nilai slug
        $this->form->slug = $slug;
    }

    public function updatedFormContent($value)
    {
        $this->form->content = $value;
    }

    public function removeImage()
    {
        $this->form->removeImage();
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.articles.edit');

            $this->form->is_published = true;
            $this->form->update();
            toastr()->success('Artikel berhasil diperbarui dan dipublikasikan');
        } else {
            $this->authorize('access', 'admin.articles.create');

            $this->form->is_published = true;
            $this->form->store();
            $this->form->reset();
            toastr()->success('Artikel berhasil disimpan dan dipublikasikan');
        }

        return redirect()->route('admin.articles.index');
    }

    public function preview()
    {
        $this->previewContent = $this->form->content;

        $this->dispatch('open-modal', 'preview-modal');
    }

    public function confirmDelete()
    {
        $this->authorize('access', 'admin.articles.destroy');

        $this->dispatch('open-modal', 'delete-article-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.articles.destroy');

        $this->form->delete();
        $this->form->reset();
        toastr()->success('Artikel berhasil dihapus');
        return redirect()->route('admin.articles.index');
    }

    public function forceDelete()
    {
        $this->authorize('access', 'admin.articles.destroy');

        $this->form->forceDelete();
        $this->form->reset();
        toastr()->success('Artikel berhasil dihapus permanen');
        return redirect()->route('admin.articles.index');
    }

    public function saveDraft()
    {
        $this->form->is_published = false;

        if ($this->editId) {
            $this->authorize('access', 'admin.articles.edit');

            $this->form->update();
            toastr()->success('Artikel berhasil diperbarui sebagai draft');
        } else {
            $this->authorize('access', 'admin.articles.create');

            $this->form->store();
            toastr()->success('Artikel berhasil disimpan sebagai draft');
        }

        $this->form->reset();
        return redirect()->route('admin.articles.index');
    }

    public function unpublish()
    {
        $this->authorize('access', 'admin.articles.unpublish');

        $this->form->unpublish();
        $this->form->reset();
        toastr()->success('Artikel berhasil di unpublish');
        return redirect()->route('admin.articles.index');
    }

    public function render()
    {
        return view('livewire.admin.pages.article.form');
    }
}
