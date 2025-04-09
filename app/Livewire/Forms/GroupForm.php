<?php

namespace App\Livewire\Forms;

use App\Models\Group;
use Livewire\Attributes\Validate;
use Livewire\Form;

class GroupForm extends Form
{
    public ?Group $group;

    #[Validate('required')]
    public string $name = '';

    public function setGroup(?Group $group)
    {
        $this->group = $group;
        $this->name = $group->name;
    }

    public function store()
    {
        $this->validate();

        Group::create([
            'name' => $this->name,
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->group->update([
            'name' => $this->name,
        ]);

        $this->reset();
    }
    public function delete()
    {
        if ($this->group) {
            $this->group->delete();
            $this->reset();
        }
    }
}
