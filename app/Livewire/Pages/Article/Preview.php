<?php

namespace App\Livewire\Pages\Article;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use App\Services\SEOService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Preview extends Component
{
    #[Layout('components.layouts.app')]

    public $article;
    public $relatedArticles;
    public bool $isPreview = true;
    public string $token;
    public ?string $featuredImageUrl = null;

    public function mount(string $token, SEOService $seo)
    {
        $this->token = $token;

        // Get preview data from session
        $previewData = Session::get("article_preview_{$token}");

        if (!$previewData) {
            abort(404, 'Preview tidak ditemukan atau sudah expired.');
        }

        // Build fake Article object from preview data
        $this->article = new Article();
        $this->article->id = $previewData['id'] ?? null;
        $this->article->title = $previewData['title'] ?? 'Judul Artikel';
        $this->article->slug = $previewData['slug'] ?? 'preview';
        $this->article->content = $previewData['content'] ?? '';
        $this->article->excerpt = $previewData['excerpt'] ?? '';
        $this->article->featured_image = $previewData['featured_image'] ?? null;
        $this->article->reading_time = $previewData['reading_time'] ?? 1;
        $this->article->views = 0;
        $this->article->is_published = false;
        $this->article->published_at = now();
        $this->article->created_at = now();
        $this->article->updated_at = now();

        // Store temporary image URL for new uploads (not saved to disk yet)
        $this->featuredImageUrl = $previewData['featured_image_url'] ?? null;

        // Set SEO title
        $seo->setTitle('Preview: ' . $this->article->title);

        // Set user relation
        $this->article->setRelation('user', Auth::user() ?? new User(['name' => 'Penulis']));

        // Set category relation
        $category = null;
        if (!empty($previewData['category_id'])) {
            $category = ArticleCategory::find($previewData['category_id']);
        }
        $this->article->setRelation('category', $category ?? new ArticleCategory(['name' => 'Umum']));

        // Set tags - tagsData is array of arrays with 'id' and 'name' keys
        $tagsData = $previewData['tags'] ?? [];
        $tagNames = collect($tagsData)->map(function ($tag) {
            return (object) ['name' => $tag['name'] ?? $tag];
        });
        $this->article->setRelation('tags', $tagNames);

        // No related articles for preview
        $this->relatedArticles = collect();
    }

    public function render()
    {
        return view('livewire.pages.article.preview');
    }
}
