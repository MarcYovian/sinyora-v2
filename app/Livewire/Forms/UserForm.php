<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user;

    public $rolePermissions = [];
    public $directPermissions = [];

    #[Validate('required|string|max:255')]
    public string $name = '';
    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:255')]
    public string $role = '';

    public function setUser(?User $user): void
    {
        $this->user = $user;

        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role =  $user->roles->isNotEmpty() ? $user->roles->first()->name : '';
        }
    }

    public function store()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make('password'),
        ]);

        $user->assignRole($this->role);

        $this->reset(['name', 'email', 'role']);
    }

    public function update()
    {
        $this->validate();

        if ($this->user) {
            $this->user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            $this->user->syncRoles([$this->role]);
        }
        $this->reset(['name', 'email', 'role']);
    }
    public function destroy()
    {
        if ($this->user) {
            $this->user->roles()->detach();
            $this->user->delete();
            $this->reset();
            return true;
        }
        return false;
    }

    public function resetPassword()
    {
        if ($this->user) {
            $this->user->update([
                'password' => Hash::make('password'),
            ]);
        }
        $this->reset(['name', 'email']);
    }

    public function assignPermission()
    {
        $this->rolePermissions = $this->user->getPermissionsViaRoles()->pluck('name')->toArray();
        $this->directPermissions = $this->user->getDirectPermissions()->pluck('name')->toArray();
    }

    public function syncPermissions()
    {
        $this->user->syncPermissions($this->directPermissions);
        $this->reset();
    }
}
