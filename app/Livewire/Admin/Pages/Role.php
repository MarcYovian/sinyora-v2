<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\RoleForm;
use App\Models\Group;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends Component
{
    use WithPagination, AuthorizesRequests;
    #[Layout('layouts.app')]

    public RoleForm $form;
    public ?SpatieRole $role;
    public $search = '';
    public $searchPermission = '';
    public $editId = null;
    public $deleteId = null;
    public $groups;


    // Toggle all permissions in a group
    public function toggleGroup($groupId)
    {
        $group = Group::with('permissions:id,name')->find($groupId);
        $permissionNames = $group->permissions->pluck('name')->toArray();

        if ($this->isAllSelected($group)) {
            $this->form->selectedPermissions = array_values(array_diff(
                $this->form->selectedPermissions,
                $permissionNames
            ));
        } else {
            $this->form->selectedPermissions = array_unique(array_merge(
                $this->form->selectedPermissions,
                $permissionNames
            ));
        }
    }

    // Check if all permissions in group are selected
    public function isAllSelected($group)
    {
        $groupPermissionNames = $group->permissions->pluck('name')->toArray();
        return count(array_intersect($groupPermissionNames, $this->form->selectedPermissions)) === count($groupPermissionNames);
    }

    // Check if search has no results
    public function hasNoResults()
    {
        if (empty($this->searchPermission)) return false;

        return Group::with(['permissions' => function ($query) {
            $query->where('name', 'like', '%' . $this->searchPermission . '%');
        }])->get()->flatMap->permissions->isEmpty();
    }

    public function create()
    {
        $this->authorize('access', 'admin.roles.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'role-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.roles.edit');

        $this->editId = $id;
        $role = SpatieRole::find($id);
        $this->form->setRole($role);
        $this->dispatch('open-modal', 'role-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.roles.edit');

            $this->form->update();
            $this->editId = null;
            flash()->success('Role updated successfully');
        } else {
            $this->authorize('access', 'admin.roles.create');

            $this->form->store();
            $this->editId = null;
            flash()->success('Role created successfully');
        }
        $this->dispatch('close-modal', 'role-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.roles.destroy');

        $this->deleteId = $id;
        $role = SpatieRole::find($id);
        $this->form->setRole($role);
        $this->dispatch('open-modal', 'delete-role-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.roles.destroy');

        if ($this->deleteId) {
            $this->form->destroy();
            $this->deleteId = null;
            flash()->success('Role deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-role-confirmation');
    }

    public function permission($id)
    {
        $this->form->setRole(SpatieRole::find($id));
        $this->form->assignPermission();

        $this->groups = Group::with(['permissions' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get(['id', 'name']);

        $this->dispatch('open-modal', 'permission-modal');
    }

    public function syncPermission()
    {
        $this->form->syncPermissions();
        flash()->success('Permissions updated successfully');
        $this->dispatch('close-modal', 'permission-modal');
    }

    public function render()
    {
        $this->authorize('access', 'admin.roles.index');

        $table_heads = ['#', 'Name', 'Actions'];

        $roles = SpatieRole::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->latest()->paginate(10);

        return view('livewire.admin.pages.role', [
            'roles' => $roles,
            'table_heads' => $table_heads
        ]);
    }
}
