<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\UserForm;
use App\Models\Group;
use App\Models\User as UserModal;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class User extends Component
{
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
            $this->form->update();
            $this->dispatch('close-modal', 'user-modal');
            $this->dispatch('alert', ['type' => 'success', 'message' => 'User updated successfully.']);
        } else {
            $this->form->store();
            $this->dispatch('close-modal', 'user-modal');
            $this->dispatch('alert', ['type' => 'success', 'message' => 'User created successfully.']);
        }

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
    }

    public function create()
    {
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'user-modal');
    }

    public function edit($id)
    {
        $this->editId = $id;
        $user = UserModal::find($id);
        $this->form->setUser($user);
        $this->dispatch('open-modal', 'user-modal');
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $user = UserModal::find($id);
        $this->form->setUser($user);
        $this->dispatch('open-modal', 'delete-menu-confirmation');
    }
    public function delete()
    {
        if ($this->form->destroy()) {
            $this->dispatch('alert', ['type' => 'success', 'message' => 'User deleted successfully.']);
        } else {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'User not deleted.']);
        }
        $this->dispatch('close-modal', 'delete-menu-confirmation');
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
    }

    public function confirmResetPassword($id)
    {
        $this->editId = $id;
        $user = UserModal::find($id);
        $this->form->setUser($user);
        $this->dispatch('open-modal', 'reset-password-confirmation');
    }

    public function resetPassword()
    {
        $this->form->resetPassword();
        $this->dispatch('alert', ['type' => 'success', 'message' => 'User password reset successfully.']);
        $this->dispatch('close-modal', 'reset-password-confirmation');
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
    }

    public function permission($id)
    {
        $this->form->setUser(UserModal::find($id));
        $this->form->assignPermission();

        // dd($this->form->selectedPermissions);

        $this->groups = Group::with(['permissions' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get(['id', 'name']);

        $this->dispatch('open-modal', 'permission-modal');
    }

    public function syncPermission()
    {
        $this->form->syncPermissions();
        $this->dispatch('updateSuccess');
        $this->dispatch('close-modal', 'permission-modal');
    }

    public function mount()
    {
        $this->roles = Role::all();
    }

    public function render()
    {
        $table_heads = ['#', 'Name', 'Email', 'Email Verified At', 'Actions'];

        $users = UserModal::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%');
        })->latest()->paginate(10);

        return view('livewire.admin.pages.user', compact('table_heads', 'users'));
    }
}
