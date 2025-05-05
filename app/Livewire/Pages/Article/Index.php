<?php

namespace App\Livewire\Pages\Article;

use App\Models\Article;
use App\Models\ArticleCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Layout('components.layouts.app')]
    #[Title('Artikel')]

    public $selectedCategory = '';
    public $search = '';

    public function filterByCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->resetPage();
    }

    public function render()
    {
        $categories = ArticleCategory::all();
        $popularCategories = ArticleCategory::withCount('articles')
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
