<?php

namespace App\Livewire\Admin\Pages\Article;

use App\Livewire\Forms\ArticleForm;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Form extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    public ArticleForm $form;

    public $editId;
    public $categories;
    public $tags;
    public $previewContent = '';

    public function mount($id = null)
    {
        if ($id) {
            // Jika edit
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
            $this->form->is_published = true;
            $this->form->update();
        } else {
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
        // dd($this->previewContent);
        $this->dispatch('open-modal', 'preview-modal');
    }

    public function confirmDelete()
    {
        $this->dispatch('open-modal', 'delete-article-confirmation');
    }

    public function delete()
    {
        $this->form->delete();
        $this->form->reset();
        toastr()->success('Artikel berhasil dihapus');
        return redirect()->route('admin.articles.index');
    }

    public function forceDelete()
    {
        $this->form->forceDelete();
        $this->form->reset();
        toastr()->success('Artikel berhasil dihapus permanen');
        return redirect()->route('admin.articles.index');
    }

    public function saveDraft()
    {
        $this->form->is_published = false;

        if ($this->editId) {
            $this->form->update();
        } else {
            $this->form->store();
        }

        $this->form->reset();
        toastr()->success('Artikel berhasil disimpan sebagai draft');
        return redirect()->route('admin.articles.index');
    }

    public function unpublish()
    {
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
