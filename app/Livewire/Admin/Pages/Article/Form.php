<?php

namespace App\Livewire\Admin\Pages\Article;

use App\DataTransferObjects\ArticleData;
use App\Livewire\Forms\ArticleForm;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use App\Services\ArticleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    public function mount($id = null)
    {
        $this->authorize('access', 'admin.articles.create');

        if ($id) {
            $this->authorize('access', 'admin.articles.edit');

            $this->article = Article::find($id);
            $this->form->setArticle($this->article);
        }

        $this->categories = ArticleCategory::get(['name', 'id']);
    }

    public function updatedFormTitle($title): void
    {
        $this->form->slug = Str::slug($title);
    }

    public function removeImage()
    {
        $this->form->removeImage();
    }

    public function save(bool $publish = false): void
    {
        $this->form->is_published = $publish;
        $this->form->validate();

        try {
            // Gunakan method injection untuk memanggil service
            $articleService = app(ArticleService::class);
            $articleData = ArticleData::fromLivewire($this->form);

            $articleService->saveArticle($articleData, $this->article);

            toastr()->success($this->article ? 'Artikel berhasil diperbarui' : 'Artikel berhasil disimpan');
            $this->redirect(route('admin.articles.index'), navigate: true);
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan artikel: ' . $e->getMessage(), ['exception' => $e]);
            toastr()->error('Terjadi kesalahan saat menyimpan artikel.');
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

    public function confirmDelete()
    {
        $this->authorize('access', 'admin.articles.destroy');
        $this->dispatch('open-modal', 'delete-article-confirmation');
    }

    public function delete(ArticleService $articleService)
    {
        $this->authorize('access', 'admin.articles.destroy');
        if ($articleService->deleteArticle($this->article)) {
            toastr()->success('Artikel berhasil dihapus');
            $this->redirect(route('admin.articles.index'), navigate: true);
        } else {
            toastr()->error('Gagal menghapus artikel.');
        }
    }

    public function forceDelete(ArticleService $articleService): void
    {
        $this->authorize('forceDelete', $this->article);
        try {
            $articleService->forceDeleteArticle($this->article);
            toastr()->success('Artikel berhasil dihapus permanen');
            $this->redirect(route('admin.articles.index'), navigate: true);
        } catch (\Throwable $e) {
            Log::error('Gagal hapus permanen artikel: ' . $e->getMessage(), ['exception' => $e]);
            toastr()->error('Terjadi kesalahan saat menghapus artikel.');
        }
    }

    public function unpublish(ArticleService $articleService): void
    {
        $this->authorize('update', $this->article);
        if ($articleService->unpublishArticle($this->article)) {
            toastr()->success('Publikasi artikel berhasil dibatalkan');
            $this->redirect(route('admin.articles.index'), navigate: true);
        } else {
            toastr()->error('Gagal membatalkan publikasi artikel.');
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.article.form');
    }
}
