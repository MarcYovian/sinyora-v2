<?php

namespace App\Livewire\Pages\Home;

use App\Livewire\Forms\ContactForm;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Index extends Component
{
    #[Layout('components.layouts.app')]
    #[Title('Beranda')]

    public ContactForm $contactForm;

    public function send()
    {
        $this->contactForm->store();

        toastr()->success('Pesan berhasil dikirim!');
    }

    public function render()
    {
        return view('livewire.pages.home.index');
    }
}
