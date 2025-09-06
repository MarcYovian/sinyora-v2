<?php

namespace App\Livewire\Pages\Article;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Services\SEOService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Layout('components.layouts.app')]

    public $selectedCategory = '';
    public $search = '';

    public function filterByCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->resetPage();
    }

    public function render(SEOService $seo)
    {
        $pageTitle = 'Artikel Kapel';
        $pageDescription = 'Kumpulan artikel inspiratif, berita terbaru, dan wawasan rohani dari Kapel St. Yohanes Rasul. Temukan bacaan yang menguatkan iman Anda.';

        if ($this->selectedCategory) {
            $category = ArticleCategory::find($this->selectedCategory);
            if ($category) {
                $pageTitle = 'Artikel Kategori: ' . $category->name;
                $pageDescription = 'Jelajahi semua artikel, berita, dan renungan dalam kategori ' . $category->name . ' di situs Kapel St. Yohanes Rasul.';
            }
        }

        // Panggil SEOService
        $seo->setTitle($pageTitle);
        $seo->setDescription($pageDescription);

        $seo->setKeywords([
            'artikel rohani katolik',
            'renungan harian',
            'berita gereja',
            'wawasan iman',
            'artikel kapel surabaya',
            'inspirasi katolik'
        ]);

        $seo->setOgImage(asset('images/seo/articles-page-ogimage.png'));

        $categories = ArticleCategory::all();
        $popularCategories = ArticleCategory::withCount(['articles' => function ($query) {
            $query->published();
        }])
            ->having('articles_count', '>', 0)
            ->orderByDesc('articles_count')
            ->limit(5)
            ->get();

        $recentArticles = Article::published()
            ->latest('published_at')
            ->limit(3)
            ->get();

        $articles = Article::with(['user', 'category', 'tags'])
            ->published()
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('content', 'like', '%' . $this->search . '%')
                    ->orWhere('excerpt', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when(request()->query('category'), function ($query) {
                $query->whereHas('category', function ($query) {
                    $query->where('name', request()->query('category'));
                });
            })
            ->when(request()->query('tag'), function ($query) {
                $query->whereHas('tags', function ($query) {
                    $query->where('name', request()->query('tag'));
                });
            })
            ->orderByDesc('published_at')
            ->paginate(6);

        return view('livewire.pages.article.index', [
            'articles' => $articles,
            'categories' => $categories,
            'popularCategories' => $popularCategories,
            'recentArticles' => $recentArticles
        ]);
    }
}
