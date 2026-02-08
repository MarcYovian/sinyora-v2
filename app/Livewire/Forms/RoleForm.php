<?php

namespace App\Livewire\Forms;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class RoleForm extends Form
{
    public ?Role $role = null;

    public string $name = '';

    public array $selectedPermissions = [];

    public bool $assignDefaultPermissions = true;

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

    public function store(): Role
    {
        $validated = $this->validate();

        try {
            $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

            // Only assign default permissions if checkbox is checked
            if ($this->assignDefaultPermissions) {
                $defaultPermissions = \App\Models\CustomPermission::where('default', 'Default')
                    ->pluck('name')
                    ->toArray();
                
                if (!empty($defaultPermissions)) {
                    $role->givePermissionTo($defaultPermissions);
                    Log::info('Default permissions assigned to new role', [
                        'role_id' => $role->id,
                        'role_name' => $role->name,
                        'permission_count' => count($defaultPermissions),
                    ]);
                }
            }

            Log::info('Role created via form', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'assign_default' => $this->assignDefaultPermissions,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return $role;
        } catch (\Exception $e) {
            Log::error('Failed to create role', [
                'user_id' => auth()->id(),
                'name' => $this->name,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function update(): Role
    {
        $validated = $this->validate();

        try {
            $this->role->update(['name' => $validated['name']]);

            Log::info('Role updated via form', [
                'role_id' => $this->role->id,
                'role_name' => $this->role->name,
                'changes' => $this->role->getChanges(),
                'user_id' => auth()->id(),
            ]);

            $role = $this->role;
            $this->reset();

            return $role;
        } catch (\Exception $e) {
            Log::error('Failed to update role', [
                'role_id' => $this->role?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function destroy(): bool
    {
        if (!$this->role) {
            Log::warning('Delete attempt with no role set', ['user_id' => auth()->id()]);
            return false;
        }

        try {
            $roleId = $this->role->id;
            $roleName = $this->role->name;

            $this->role->delete();

            Log::info('Role deleted via form', [
                'role_id' => $roleId,
                'role_name' => $roleName,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete role', [
                'role_id' => $this->role?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function assignPermission(): void
    {
        $this->selectedPermissions = $this->role->permissions()->pluck('name')->toArray();
    }

    public function syncPermissions(): void
    {
        if (!$this->role) {
            Log::warning('Sync permissions attempt with no role set', ['user_id' => auth()->id()]);
            return;
        }

        try {
            $roleId = $this->role->id;
            $permissionCount = count($this->selectedPermissions);

            $this->role->syncPermissions($this->selectedPermissions);

            // Clear all permission-related caches (Spatie + Sidebar)
            \App\Observers\RoleObserver::clearAllCaches();

            Log::info('Role permissions synced via form', [
                'role_id' => $roleId,
                'permission_count' => $permissionCount,
                'user_id' => auth()->id(),
            ]);

            $this->reset();
        } catch (\Exception $e) {
            Log::error('Failed to sync role permissions', [
                'role_id' => $this->role?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
