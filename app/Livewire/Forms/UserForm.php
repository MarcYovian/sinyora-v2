<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user = null;

    public $rolePermissions = [];
    public $directPermissions = [];
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $role = '';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($this->user?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user?->id)],
            'role' => ['required', 'string', 'max:255', Rule::exists('roles', 'name')],
        ];
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;

        if ($user) {
            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->role =  $user->roles->isNotEmpty() ? $user->roles->first()->name : '';
        }
    }

    public function store()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make('password'),
        ]);

        $user->assignRole($this->role);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        if ($this->user) {
            $this->user->update([
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
            ]);

            $this->user->syncRoles([$this->role]);
        }
        $this->reset();
    }
    public function destroy()
    {
        if ($this->user) {
            $this->user->roles()->detach();
            $this->user->delete();
            $this->reset();
        }
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
