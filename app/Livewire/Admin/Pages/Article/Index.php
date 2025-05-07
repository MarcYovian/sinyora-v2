<?php

namespace App\Livewire\Admin\Pages\Article;

use App\Livewire\Forms\ArticleForm;
use App\Models\Article;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public ArticleForm $form;
    public ?Article $article = null;

    public $search = '';

    public function show(Article $article)
    {
        $this->authorize('access', 'admin.articles.show');

        $this->article = $article->load(['user', 'category', 'tags']);
        $this->dispatch('open-modal', 'preview-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.articles.destroy');

        $article = Article::findOrFail($id);
        $this->form->setArticle($article);

        $this->dispatch('open-modal', 'delete-article-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.articles.destroy');

        $this->form->delete();
        $this->form->reset();
        toastr()->success('Artikel berhasil dihapus');

        $this->dispatch('close-modal', 'delete-article-confirmation');
    }

    public function forceDelete()
    {
        $this->authorize('access', 'admin.articles.destroy');

        $this->form->forceDelete();
        $this->form->reset();
        toastr()->success('Artikel berhasil dihapus permanen');

        $this->dispatch('close-modal', 'delete-article-confirmation');
    }

    public function render()
    {
        // Authorization
        $this->authorize('access', 'admin.articles.index');

        $table_heads = ['#', 'Thumbnail', 'Title', 'Published Date', 'Status', 'Category', 'Author', 'Actions'];

        $articles = Article::with(['user', 'category', 'tags'])
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->latest()->paginate(10);

        return view('livewire.admin.pages.article.index', [
            'table_heads' => $table_heads,
            'articles' => $articles
        ]);
    }
}
