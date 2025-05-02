<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\PermissionForm;
use App\Models\CustomPermission;
use App\Models\Group;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Permission extends Component
{
    use WithPagination, AuthorizesRequests;
    #[Layout('layouts.app')]

    public PermissionForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public $groups;
    public function create()
    {
        $this->authorize('access', 'admin.permissions.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'permission-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.permissions.edit');

        $this->editId = $id;
        $permission = CustomPermission::find($id);
        $this->form->setPermission($permission);
        $this->dispatch('open-modal', 'permission-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.permissions.edit');

            $this->form->update();
            $this->editId = null;
            toastr()->success('Permission updated successfully');
        } else {
            $this->authorize('access', 'admin.permissions.create');

            $this->form->store();
            toastr()->success('Permission created successfully');
        }
        $this->dispatch('close-modal', 'permission-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.permissions.destroy');

        $this->deleteId = $id;
        $permission = CustomPermission::find($id);
        $this->form->setPermission($permission);
        $this->dispatch('open-modal', 'delete-permission-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.permissions.destroy');

        if ($this->deleteId) {
            $this->form->destroy();
            $this->deleteId = null;
            toastr()->success('Permission deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-permission-confirmation');
    }

    public function mount()
    {
        $this->authorize('access', 'admin.permissions.index');

        $this->groups = Group::all();
    }

    public function render()
    {
        $this->authorize('access', 'admin.permissions.index');

        $table_heads = ['#', 'Group', 'Name', 'Route Name', 'Default', 'Actions'];

        $permissions = CustomPermission::with('groupPermission')->when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('route_name', 'like', '%' . $this->search . '%')
                ->orWhereHas('groupPermission', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                });
        })->latest()->paginate(10);

        return view('livewire.admin.pages.permission', [
            'table_heads' => $table_heads,
            'permissions' => $permissions,
        ]);
    }
}
