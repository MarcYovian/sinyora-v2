<?php

namespace App\Livewire\Admin\Pages\Article;

use App\DataTransferObjects\ArticleData;
use App\Livewire\Forms\ArticleForm;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use App\Services\ArticleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads, AuthorizesRequests;

    #[Layout('layouts.app')]

    public ArticleForm $form;
    public ?Article $article = null;
    public $categories;
    public string $correlationId = '';

    /**
     * Mount the component.
     */
    public function mount(?int $id = null): void
    {
        $this->authorize('access', 'admin.articles.create');
        $this->correlationId = Str::uuid()->toString();

        if ($id) {
            $this->authorize('access', 'admin.articles.edit');

            $this->article = Article::find($id);
            $this->form->setArticle($this->article);
        }

        $this->categories = Cache::remember('articles.categories.all', 86400, fn() =>
            ArticleCategory::select('id', 'name')->get()
        );
    }

    public function updatedFormTitle($title): void
    {
        $this->form->slug = Str::slug($title);
    }

    /**
     * Remove uploaded image.
     */
    public function removeImage(): void
    {
        $this->form->removeImage();
    }

    public function save(bool $publish = false): void
    {
        $this->form->is_published = $publish;
        $this->form->validate();

        try {
            $articleService = app(ArticleService::class);
            $articleData = ArticleData::fromLivewire($this->form);

            $articleService->saveArticle($articleData, $this->article);

            Log::info('Article saved successfully', [
                'user_id' => Auth::id(),
                'article_id' => $this->article?->id,
                'is_published' => $publish,
                'correlation_id' => $this->correlationId,
            ]);

            flash()->success($this->article ? 'Artikel berhasil diperbarui' : 'Artikel berhasil disimpan');
            $this->redirect(route('admin.articles.index'), navigate: true);
        } catch (\Throwable $e) {
            Log::error('Failed to save article', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Terjadi kesalahan saat menyimpan artikel. #{$this->correlationId}");
        }
    }

    public function saveAndPublish(): void
    {
        $this->save(true);
    }

    public function saveAsDraft(): void
    {
        $this->save(false);
    }

    public function preview()
    {
        // Generate unique token for this preview
        $token = \Illuminate\Support\Str::random(32);

        // Handle image - either temporary upload or existing image
        $imageUrl = null;
        $imagePath = null;
        if ($this->form->image) {
            // New upload - get temporary URL
            $imageUrl = $this->form->image->temporaryUrl();
        } elseif ($this->form->featured_image) {
            // Existing image - store the path
            $imagePath = $this->form->featured_image;
        }

        // Prepare preview data
        $previewData = [
            'id' => $this->article?->id,
            'title' => $this->form->title,
            'slug' => $this->form->slug,
            'content' => $this->form->content,
            'excerpt' => $this->form->excerpt,
            'category_id' => $this->form->category_id,
            'featured_image' => $imagePath,
            'featured_image_url' => $imageUrl, // Temporary URL for new uploads
            'reading_time' => $this->calculateReadingTime($this->form->content),
            'tags' => $this->form->tags,
        ];

        // Store in session for 30 minutes
        session()->put("article_preview_{$token}", $previewData);

        // Generate preview URL
        $previewUrl = route('articles.preview', ['token' => $token]);

        // Dispatch event to frontend to open new tab
        $this->dispatch('open-preview', url: $previewUrl);
    }

    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $readingTime = ceil($wordCount / 200); // Average 200 words per minute
        return max(1, $readingTime);
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(): void
    {
        $this->authorize('access', 'admin.articles.destroy');
        $this->dispatch('open-modal', 'delete-article-confirmation');
    }

    /**
     * Soft delete the article.
     */
    public function delete(ArticleService $articleService): void
    {
        $this->authorize('access', 'admin.articles.destroy');
        if ($articleService->deleteArticle($this->article)) {
            Log::info('Article deleted from form page', [
                'user_id' => Auth::id(),
                'article_id' => $this->article?->id,
                'correlation_id' => $this->correlationId,
            ]);
            flash()->success('Artikel berhasil dihapus');
            $this->redirect(route('admin.articles.index'), navigate: true);
        } else {
            flash()->error('Gagal menghapus artikel.');
        }
    }

    /**
     * Force delete the article permanently.
     */
    public function forceDelete(ArticleService $articleService): void
    {
        $this->authorize('forceDelete', $this->article);
        try {
            $articleService->forceDeleteArticle($this->article);
            Log::info('Article force deleted from form page', [
                'user_id' => Auth::id(),
                'article_id' => $this->article?->id,
                'correlation_id' => $this->correlationId,
            ]);
            flash()->success('Artikel berhasil dihapus permanen');
            $this->redirect(route('admin.articles.index'), navigate: true);
        } catch (\Throwable $e) {
            Log::error('Failed to force delete article', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Terjadi kesalahan saat menghapus artikel. #{$this->correlationId}");
        }
    }

    public function unpublish(ArticleService $articleService): void
    {
        $this->authorize('update', $this->article);
        if ($articleService->unpublishArticle($this->article)) {
            flash()->success('Publikasi artikel berhasil dibatalkan');
            $this->redirect(route('admin.articles.index'), navigate: true);
        } else {
            flash()->error('Gagal membatalkan publikasi artikel.');
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.article.form');
    }
}
