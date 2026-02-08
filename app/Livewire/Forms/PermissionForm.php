<?php

namespace App\Livewire\Forms;

use App\Models\CustomPermission;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PermissionForm extends Form
{
    public ?CustomPermission $permission = null;

    public string $name = '';
    public int $group = 0;
    public string $route_name = '';
    public string $default = '';

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->ignore($this->permission?->id),
            ],
            'group' => [
                'required',
                'exists:groups,id',
            ],
            'route_name' => [
                'required',
                'string',
                'max:255',
            ],
            'default' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    public function setPermission(?CustomPermission $permission): void
    {
        $this->permission = $permission;
        $this->name = $permission->name;
        $this->group = $permission->group;
        $this->route_name = $permission->route_name;
        $this->default = $permission->default;
    }

    public function store(): CustomPermission
    {
        $validated = $this->validate();

        try {
            $permission = CustomPermission::create([
                'name' => $validated['name'],
                'group' => $validated['group'],
                'route_name' => $validated['route_name'],
                'default' => $validated['default'],
                'guard_name' => 'web',
            ]);

            Log::info('Permission created via form', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name,
                'route_name' => $permission->route_name,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return $permission;
        } catch (\Exception $e) {
            Log::error('Failed to create permission', [
                'user_id' => auth()->id(),
                'name' => $this->name,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function update(): CustomPermission
    {
        $validated = $this->validate();

        try {
            $this->permission->update([
                'name' => $validated['name'],
                'group' => $validated['group'],
                'route_name' => $validated['route_name'],
                'default' => $validated['default'],
                'guard_name' => 'web',
            ]);

            Log::info('Permission updated via form', [
                'permission_id' => $this->permission->id,
                'permission_name' => $this->permission->name,
                'changes' => $this->permission->getChanges(),
                'user_id' => auth()->id(),
            ]);

            $permission = $this->permission;
            $this->reset();

            return $permission;
        } catch (\Exception $e) {
            Log::error('Failed to update permission', [
                'permission_id' => $this->permission?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function destroy(): bool
    {
        if (!$this->permission) {
            Log::warning('Delete attempt with no permission set', ['user_id' => auth()->id()]);
            return false;
        }

        try {
            $permissionId = $this->permission->id;
            $permissionName = $this->permission->name;

            $this->permission->delete();

            Log::info('Permission deleted via form', [
                'permission_id' => $permissionId,
                'permission_name' => $permissionName,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete permission', [
                'permission_id' => $this->permission?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
