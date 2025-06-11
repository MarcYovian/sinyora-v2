<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class GuestEventForm extends Form
{
    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|email|max:255')]
    public $email = '';

    #[Validate('required|string|max:500')]
    public $message = '';

    public function submit()
    {
        $this->reset();
    }
}
