<?php

namespace App\Livewire\Forms;

use App\Models\Organization;
use Livewire\Attributes\Validate;
use Livewire\Form;

class OrganizationForm extends Form
{
    public ?Organization $organization;

    #[Validate('required')]
    public string $name = '';
    #[Validate('nullable')]
    public string $description = '';
    #[Validate('required')]
    public string $code = '';
    #[Validate('required')]
    public bool $is_active = false;

    public function setOrganization(?Organization $organization)
    {
        $this->organization = $organization;
        $this->name = $organization->name;
        $this->description = $organization->description;
        $this->code = $organization->code;
        $this->is_active = $organization->is_active;
    }

    public function store()
    {
        $this->validate();

        Organization::create([
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'is_active' => $this->is_active,
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->organization->update([
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'is_active' => $this->is_active,
        ]);

        $this->reset();
    }
    public function delete()
    {
        if ($this->organization) {
            $this->organization->delete();
            $this->reset();
        }
    }
}
