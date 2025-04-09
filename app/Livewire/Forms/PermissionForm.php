<?php

namespace App\Livewire\Forms;

use App\Models\CustomPermission;
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

    public function store()
    {
        $this->validate();

        CustomPermission::create([
            'name' => $this->name,
            'group' => $this->group,
            'route_name' => $this->route_name,
            'default' => $this->default,
            'guard_name' => 'web',
        ]);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->permission->update([
            'name' => $this->name,
            'group' => $this->group,
            'route_name' => $this->route_name,
            'default' => $this->default,
            'guard_name' => 'web',
        ]);

        $this->reset();
    }

    public function destroy()
    {
        $this->permission->delete();
        $this->reset();
    }
}
