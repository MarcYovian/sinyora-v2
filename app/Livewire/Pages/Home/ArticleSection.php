<?php

namespace App\Livewire\Pages\Home;

use App\Models\Article;
use Livewire\Component;

class ArticleSection extends Component
{

    public $popularArticles;
    public $latestArticles;

    public function mount()
    {
        // Query base untuk menghindari duplikasi event di section artikel
        $baseQuery = Article::published()->whereDoesntHave('tags', function ($query) {
            $query->where('name', 'event');
        });

        $this->popularArticles = (clone $baseQuery)
            ->with('category')
            ->orderByDesc('views')
            ->take(2)
            ->get();

        $this->latestArticles = (clone $baseQuery)
            ->with('category')
            ->latest()
            ->take(3)
            ->get();
    }

    public function placeholder()
    {
        return view('livewire.loader.article-section-loader');
    }

    public function render()
    {
        return view('livewire.pages.home.article-section');
    }
}
