<?php

namespace App\Livewire\Admin\Pages\Article;

use App\Livewire\Forms\ArticleForm;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public ArticleForm $form;
    public ?Article $article = null;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 's')]
    public string $filterStatus = '';

    public string $correlationId = '';

    protected $articleService;

    public array $table_heads = ['No', 'Thumbnail', 'Title', 'Published Date', 'Status', 'Category', 'Author', 'Actions'];

    /**
     * Boot the component with dependencies.
     */
    public function boot(ArticleService $articleService): void
    {
        $this->articleService = $articleService;
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('access', 'admin.articles.index');
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Handle property updates (reset pagination on filter change).
     */
    public function updated(string $propertyName): void
    {
        if (in_array($propertyName, ['search', 'filterStatus'])) {
            $this->resetPage();
        }
    }

    /**
     * Reset all filters and pagination.
     */
    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    /**
     * Show article preview.
     */
    public function show(Article $article): void
    {
        try {
            $this->authorize('access', 'admin.articles.show');

            $this->article = $article->load(['user:id,name', 'category:id,name', 'tags:id,name']);
            $this->dispatch('open-modal', 'preview-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized article show attempt', ['user_id' => Auth::id(), 'article_id' => $article->id]);
            flash()->error('You are not authorized to view articles.');
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        try {
            $this->authorize('access', 'admin.articles.destroy');

            $this->article = Article::findOrFail($id);
            $this->dispatch('open-modal', 'delete-article-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized article delete attempt', ['user_id' => Auth::id(), 'article_id' => $id]);
            flash()->error('You are not authorized to delete articles.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Article not found for delete', ['article_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Article not found.');
        }
    }

    /**
     * Soft delete an article.
     */
    public function delete(): void
    {
        Log::info('Article deletion initiated', [
            'user_id' => Auth::id(),
            'article_id' => $this->article?->id,
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.articles.destroy');

            if ($this->articleService->deleteArticle($this->article)) {
                Log::info('Article deleted successfully', [
                    'user_id' => Auth::id(),
                    'article_id' => $this->article?->id,
                    'correlation_id' => $this->correlationId,
                ]);
                flash()->success('Artikel berhasil dihapus');
            } else {
                flash()->error('Gagal menghapus artikel.');
            }
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized article deletion', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to delete articles.');
        } catch (\Exception $e) {
            Log::error('Article deletion failed', [
                'user_id' => Auth::id(),
                'article_id' => $this->article?->id,
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Terjadi kesalahan saat menghapus artikel. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-article-confirmation');
            $this->article = null;
        }
    }

    /**
     * Force delete an article permanently.
     */
    public function forceDelete(): void
    {
        Log::info('Article force deletion initiated', [
            'user_id' => Auth::id(),
            'article_id' => $this->article?->id,
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.articles.destroy');

            $this->articleService->forceDeleteArticle($this->article);

            Log::info('Article force deleted successfully', [
                'user_id' => Auth::id(),
                'article_id' => $this->article?->id,
                'correlation_id' => $this->correlationId,
            ]);

            flash()->success('Artikel berhasil dihapus permanen');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized article force deletion', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to delete articles.');
        } catch (\Throwable $e) {
            Log::error('Article force deletion failed', [
                'user_id' => Auth::id(),
                'article_id' => $this->article?->id,
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Terjadi kesalahan saat menghapus artikel. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-article-confirmation');
            $this->article = null;
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.articles.index');

        $articles = Article::query()
            ->select(['id', 'title', 'slug', 'featured_image', 'is_published', 'published_at', 'category_id', 'created_at'])
            ->with(['user:id,name', 'category:id,name'])
            ->whereNotNull('category_id')
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('is_published', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.article.index', [
            'table_heads' => $this->table_heads,
            'articles' => $articles,
        ]);
    }
}
