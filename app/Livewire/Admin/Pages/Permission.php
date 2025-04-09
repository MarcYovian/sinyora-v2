<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\PermissionForm;
use App\Models\CustomPermission;
use App\Models\Group;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Permission extends Component
{
    use WithPagination;
    #[Layout('layouts.app')]

    public PermissionForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public $groups;
    public function create()
    {
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'permission-modal');
    }

    public function edit($id)
    {
        $this->editId = $id;
        $permission = CustomPermission::find($id);
        $this->form->setPermission($permission);
        $this->dispatch('open-modal', 'permission-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->form->update();
            $this->editId = null;
            $this->dispatch('updateSuccess');
        } else {
            $this->form->store();
            $this->dispatch('createSuccess');
        }
        $this->dispatch('close-modal', 'permission-modal');
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $permission = CustomPermission::find($id);
        $this->form->setPermission($permission);
        $this->dispatch('open-modal', 'delete-permission-confirmation');
    }

    public function delete()
    {
        if ($this->deleteId) {
            $this->form->destroy();
            $this->deleteId = null;
            $this->dispatch('deleteSuccess');
        }
        $this->dispatch('close-modal', 'delete-permission-confirmation');
    }

    public function mount()
    {
        $this->groups = Group::all();
    }

    public function render()
    {
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
