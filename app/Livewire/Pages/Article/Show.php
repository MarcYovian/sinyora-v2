<?php

namespace App\Livewire\Pages\Article;

use App\Models\Article;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{

    #[Layout('components.layouts.app')]

    public Article $article;
    public $relatedArticles;

    public function mount(Article $article)
    {
        $this->article = $article;
        $this->article->increment('views');
        $this->article->load(['user', 'category', 'tags']);

        $this->relatedArticles = Article::with(['user', 'category', 'tags'])
            ->published()
            ->where('id', '!=', $this->article->id)
            ->where('category_id', $this->article->category_id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.article.show')
            ->title($this->article->title);
    }
}
