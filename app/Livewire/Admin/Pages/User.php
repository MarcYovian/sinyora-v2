<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\UserForm;
use App\Models\Group;
use App\Models\User as UserModal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class User extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]


    public UserForm $form;

    public $roles;

    public $search = '';
    public $searchPermission = '';
    public $groups;
    public $editId = null;
    public $deleteId = null;


    // Check if search has no results
    public function hasNoResults()
    {
        if (empty($this->searchPermission)) return false;

        return Group::with(['permissions' => function ($query) {
            $query->where('name', 'like', '%' . $this->searchPermission . '%');
        }])->get()->flatMap->permissions->isEmpty();
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.users.edit');

            $this->form->update();
            $this->dispatch('close-modal', 'user-modal');
            toastr()->success('User updated successfully.');
        } else {
            $this->authorize('access', 'admin.users.create');

            $this->form->store();
            $this->dispatch('close-modal', 'user-modal');
            toastr()->success('User created successfully.');
        }

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
    }

    public function create()
    {
        $this->authorize('access', 'admin.users.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'user-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.users.edit');

        $this->editId = $id;
        $user = UserModal::find($id);
        $this->form->setUser($user);
        $this->dispatch('open-modal', 'user-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.users.destroy');

        $this->deleteId = $id;
        $user = UserModal::find($id);
        $this->form->setUser($user);
        $this->dispatch('open-modal', 'delete-user-confirmation');
    }
    public function delete()
    {
        $this->authorize('access', 'admin.users.destroy');

        $this->form->destroy();
        toastr()->success('User deleted successfully.');

        $this->dispatch('close-modal', 'delete-menu-confirmation');
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
    }

    public function confirmResetPassword($id)
    {
        $this->authorize('access', 'password.reset');
        $this->editId = $id;
        $user = UserModal::find($id);
        $this->form->setUser($user);
        $this->dispatch('open-modal', 'reset-password-confirmation');
    }

    public function resetPassword()
    {
        $this->authorize('access', 'password.reset');

        $this->form->resetPassword();
        toastr()->success('Password reset successfully.');
        $this->dispatch('close-modal', 'reset-password-confirmation');
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
    }

    public function permission($id)
    {
        $this->authorize('access', 'admin.users.role-permission');

        $this->form->setUser(UserModal::find($id));
        $this->form->assignPermission();

        $this->groups = Group::with(['permissions' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get(['id', 'name']);

        $this->dispatch('open-modal', 'permission-modal');
    }

    public function syncPermission()
    {
        $this->authorize('access', 'admin.users.role-permission');

        $this->form->syncPermissions();
        toastr()->success('Permissions updated successfully.');
        $this->dispatch('close-modal', 'permission-modal');
    }

    public function mount()
    {
        $this->authorize('access', 'admin.users.index');

        $this->roles = Role::all();
    }

    public function render()
    {
        $this->authorize('access', 'admin.users.index');

        $table_heads = ['#', 'Name', 'Username', 'Email', 'Email Verified At', 'Actions'];

        $users = UserModal::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('username', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%');
        })->latest()->paginate(10);

        return view('livewire.admin.pages.user', compact('table_heads', 'users'));
    }
}
