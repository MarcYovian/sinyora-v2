<?php

namespace App\Livewire\Pages\Home;

use App\Models\Article;
use Livewire\Component;

class EventSection extends Component
{
    public $eventArticles;

    public function mount()
    {
        $this->eventArticles = Article::published()
            ->whereHas('tags', function ($query) {
                $query->where('name', 'event');
            })
            ->with(['category', 'tags'])
            ->latest()
            ->take(3)
            ->get();
    }

    public function placeholder()
    {
        return view('livewire.loader.event-section-loader');
    }

    public function render()
    {
        return view('livewire.pages.home.event-section');
    }
}
