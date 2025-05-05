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
        $this->popularArticles = Article::with('category')
            ->orderByDesc('views')
            ->take(2)
            ->get();

        $this->latestArticles = Article::latest()->take(3)->get();
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
