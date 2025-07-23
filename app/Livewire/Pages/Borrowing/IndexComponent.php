<?php

namespace App\Livewire\Pages\Borrowing;

use App\Models\Asset;
use App\Models\Borrowing;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class IndexComponent extends Component
{
    #[Layout('components.layouts.app')]
    #[Title('Jadwal Peminjaman')]

    public $assets;
    public $borrowings;

    public function mount()
    {
        $this->assets = Asset::with('assetCategory')->latest()->get();
        $this->borrowings = Borrowing::with(['event', 'assets', 'creator'])
            ->approved()
            ->where('end_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->get();
    }

    public function createRequest()
    {
        $this->dispatch('open-modal', 'proposal-modal');
    }

    public function render()
    {
        return view('livewire.pages.borrowing.index-component');
    }
}
