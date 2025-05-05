<?php

namespace App\Livewire\Pages\Home;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Index extends Component
{
    #[Layout('components.layouts.app')]
    #[Title('Home')]

    public function render()
    {
        return view('livewire.pages.home.index');
    }
}
