<?php

namespace App\Livewire\Admin\Pages\Article;

use App\Livewire\Forms\ArticleForm;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
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

    protected $articleService;

    public function boot(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function show(Article $article)
    {
        $this->authorize('access', 'admin.articles.show');

        $this->article = $article->load(['user', 'category', 'tags']);
        $this->dispatch('open-modal', 'preview-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.articles.destroy');
        $this->article = Article::findOrFail($id);
        $this->dispatch('open-modal', 'delete-article-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.articles.destroy');

        if ($this->articleService->deleteArticle($this->article)) {
            flash()->success('Artikel berhasil dihapus');
        } else {
            flash()->error('Gagal menghapus artikel.');
        }

        $this->dispatch('close-modal', 'delete-article-confirmation');
        $this->article = null;
    }

    public function forceDelete()
    {
        $this->authorize('access', 'admin.articles.destroy');

        try {
            $this->articleService->forceDeleteArticle($this->article);
            flash()->success('Artikel berhasil dihapus permanen');
        } catch (\Throwable $e) {
            Log::error('Gagal hapus permanen artikel: ' . $e->getMessage(), ['exception' => $e]);
            flash()->error('Terjadi kesalahan saat menghapus artikel.');
        }
        $this->dispatch('close-modal', 'delete-article-confirmation');
        $this->article = null;
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
