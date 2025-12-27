<?php

namespace App\Livewire\Pages\Article;

use App\Models\Article;
use App\Services\SEOService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    #[Layout('components.layouts.app')]

    public Article $article;
    public $relatedArticles;

    public function mount(Article $article, SEOService $seo)
    {
        $this->article = $article;
        $this->article->increment('views');
        $this->article->load(['user', 'category', 'tags']);

        // Set SEO data
        $seo->setTitle($this->article->title)
            ->setDescription($this->article->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($this->article->content), 160))
            ->setKeywords(
                $this->article->tags->pluck('name')->merge([
                    'artikel',
                    'kapel',
                    'st yohanes rasul',
                    $this->article->category->name ?? 'berita',
                ])->toArray()
            )
            ->setCanonical(route('articles.show', $this->article))
            ->setOgType('article'); // Set og:type to article

        // Set OG Image if featured image exists
        if ($this->article->featured_image) {
            $seo->setOgImage(Storage::url($this->article->featured_image));
        }

        // Set Breadcrumbs
        $seo->setBreadcrumbs([
            ['name' => 'Home', 'url' => route('home.index')],
            ['name' => 'Artikel', 'url' => route('articles.index')],
            ['name' => $this->article->category->name ?? 'Kategori', 'url' => route('articles.index', ['category' => $this->article->category->name ?? ''])],
            ['name' => $this->article->title], // Last item without URL
        ]);

        // Set Article Schema with timeRequired
        $seo->setSchema([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->article->title,
            'description' => $this->article->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($this->article->content), 160),
            'image' => $this->article->featured_image ? Storage::url($this->article->featured_image) : null,
            'datePublished' => $this->article->published_at?->toIso8601String(),
            'dateModified' => $this->article->updated_at->toIso8601String(),
            'timeRequired' => 'PT' . $this->article->reading_time . 'M', // ISO 8601 duration format
            'wordCount' => str_word_count(strip_tags($this->article->content)),
            'author' => [
                '@type' => 'Person',
                'name' => $this->article->user->name ?? 'Admin',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Kapel St. Yohanes Rasul',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('articles.show', $this->article),
            ],
        ]);

        // Cache related articles for 1 hour
        $this->relatedArticles = Cache::remember(
            'articles.related.' . $this->article->category_id . '.exclude.' . $this->article->id,
            3600, // 1 hour
            fn() => Article::with(['user', 'category', 'tags'])
                ->published()
                ->where('id', '!=', $this->article->id)
                ->where('category_id', $this->article->category_id)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
        );
    }

    public function render()
    {
        return view('livewire.pages.article.show');
    }
}
