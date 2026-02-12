<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\RoleForm;
use App\Models\Group;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends Component
{
    use WithPagination, AuthorizesRequests;
    
    #[Layout('layouts.app')]

    public RoleForm $form;
    public ?SpatieRole $role;

    #[Url(as: 'q')]
    public $search = '';

    public $searchPermission = '';
    public ?int $editId = null;
    public ?int $deleteId = null;
    public $groups;
    public string $correlationId = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

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
        try {
            $this->authorize('access', 'admin.roles.edit');

            $role = SpatieRole::findOrFail($id);
            $this->editId = $id;
            $this->form->setRole($role);
            $this->dispatch('open-modal', 'role-modal');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Role not found for edit', ['role_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Role not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized role edit attempt', ['role_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to edit this role.');
        } catch (\Exception $e) {
            Log::error('Failed to load role for edit', [
                'role_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to load role. Please try again.');
        }
    }

    public function save()
    {
        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.roles.edit');

                $this->form->update();
                $this->editId = null;
                flash()->success('Role updated successfully');
                Log::info('Role updated via Livewire', ['user_id' => auth()->id()]);
            } else {
                $this->authorize('access', 'admin.roles.create');

                $this->form->store();
                $this->editId = null;
                flash()->success('Role created successfully');
                Log::info('Role created via Livewire', ['user_id' => auth()->id()]);
            }
            $this->dispatch('close-modal', 'role-modal');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized role save attempt', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (\Exception $e) {
            Log::error('Failed to save role', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to save role. Please try again.');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $this->authorize('access', 'admin.roles.destroy');

            $role = SpatieRole::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setRole($role);
            $this->dispatch('open-modal', 'delete-role-confirmation');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Role not found for delete confirmation', ['role_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Role not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized role delete attempt', ['role_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to delete this role.');
        } catch (\Exception $e) {
            Log::error('Failed to prepare role deletion', [
                'role_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to prepare deletion. Please try again.');
        }
    }

    public function delete()
    {
        try {
            $this->authorize('access', 'admin.roles.destroy');

            if ($this->deleteId) {
                $roleName = $this->form->name;
                $this->form->destroy();
                $this->deleteId = null;

                flash()->success('Role deleted successfully');
                Log::info('Role deleted via Livewire', [
                    'role_name' => $roleName,
                    'user_id' => auth()->id(),
                ]);
            }
            $this->dispatch('close-modal', 'delete-role-confirmation');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized role delete attempt', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
            ]);
            flash()->error('You are not authorized to delete this role.');
        } catch (\Exception $e) {
            Log::error('Failed to delete role', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to delete role. Please try again.');
        }
    }

    public function permission($id)
    {
        try {
            $this->authorize('access', 'admin.roles.edit');

            $role = SpatieRole::findOrFail($id);
            $this->form->setRole($role);
            $this->form->assignPermission();

            $this->groups = Group::with(['permissions' => function ($query) {
                $query->orderBy('name');
            }])->orderBy('name')->get(['id', 'name']);

            $this->dispatch('open-modal', 'permission-modal');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Role not found for permission assignment', ['role_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Role not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized permission assignment attempt', ['role_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to modify permissions.');
        } catch (\Exception $e) {
            Log::error('Failed to load role permissions', [
                'role_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to load permissions. Please try again.');
        }
    }

    public function syncPermission()
    {
        try {
            $this->authorize('access', 'admin.roles.edit');

            $this->form->syncPermissions();
            
            // Dispatch event to refresh sidebar in real-time
            $this->dispatch('menuUpdated');
            
            flash()->success('Permissions updated successfully');
            Log::info('Role permissions synced via Livewire', [
                'role_id' => $this->form->role?->id,
                'user_id' => auth()->id(),
            ]);
            $this->dispatch('close-modal', 'permission-modal');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized permission sync attempt', ['user_id' => auth()->id()]);
            flash()->error('You are not authorized to modify permissions.');
        } catch (\Exception $e) {
            Log::error('Failed to sync permissions', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to update permissions. Please try again.');
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('access', 'admin.roles.index');
        $this->correlationId = Str::uuid()->toString();
    }

    public function render()
    {
        $this->authorize('access', 'admin.roles.index');

        $table_heads = ['No', 'Name', 'Actions'];

        $roles = SpatieRole::query()
            ->select(['id', 'name', 'guard_name', 'created_at'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })->latest()->paginate(10);

        return view('livewire.admin.pages.role', [
            'roles' => $roles,
            'table_heads' => $table_heads
        ]);
    }
}
