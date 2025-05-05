<?php

namespace App\Livewire\Forms;

use App\Models\Contact;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ContactForm extends Form
{
    public ?Contact $contact = null;
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';
    #[Validate('required|max:20')]
    public string $phone = '';

    #[Validate('required|string|max:500')]
    public string $message = '';

    public function setContact(Contact $contact): void
    {
        $this->contact = $contact;
        $this->name = $contact->name;
        $this->email = $contact->email;
        $this->phone = $contact->phone;
        $this->message = $contact->message;
    }

    public function store()
    {
        $this->validate();

        Contact::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'status' => 'new',
        ]);

        $this->reset(['name', 'email', 'phone', 'message']);
    }

    public function edit()
    {
        $this->validate();

        if ($this->contact) {
            $this->contact->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'message' => $this->message,
            ]);
        }

        $this->reset(['name', 'email', 'phone', 'message']);
    }
}
