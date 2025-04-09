<?php

namespace App\Livewire\Layout;

use App\Models\Menu;
use Livewire\Attributes\On;
use Livewire\Component;

class Sidebar extends Component
{
    #[On('menuUpdated')]
    public function render()
    {
        $menus = Menu::where('is_active', true)
            ->orderBy('sort')
            ->get()
            ->groupBy('main_menu');

        return view('livewire.layout.sidebar', compact('menus'));
    }
}
