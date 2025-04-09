<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class RoleForm extends Form
{
    public ?Role $role = null;

    public string $name = '';

    public array $selectedPermissions = [];

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($this->role?->id),
            ],
        ];
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
        $this->name = $role->name;
    }

    public function store()
    {
        $this->validate();

        Role::create(['name' => $this->name, 'guard_name' => 'web']);
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->role->update(['name' => $this->name]);
        $this->reset();
    }

    public function destroy()
    {
        $this->role->delete();
        $this->reset();
    }

    public function assignPermission()
    {
        $this->selectedPermissions = $this->role->permissions()->pluck('name')->toArray();
    }

    public function syncPermissions()
    {
        $this->role->syncPermissions($this->selectedPermissions);
        $this->reset();
    }
}
