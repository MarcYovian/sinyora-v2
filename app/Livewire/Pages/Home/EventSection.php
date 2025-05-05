<?php

namespace App\Livewire\Pages\Home;

use App\Models\Article;
use Livewire\Component;

class EventSection extends Component
{
    public $eventArticles;

    public function mount()
    {
        $this->eventArticles = Article::whereHas('tags', function ($query) {
            $query->where('name', 'event');
        })
            ->with(['tags' => function ($query) {
                $query->where('name', 'event'); // Eager load hanya tag event
            }])
            ->latest()
            ->take(2)
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
