<?php

namespace App\Livewire\Pages\Borrowing;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class IndexComponent extends Component
{
    #[Layout('components.layouts.app')]
    #[Title('Jadwal Peminjaman')]

    public function render()
    {
        return view('livewire.pages.borrowing.index-component');
    }
}
