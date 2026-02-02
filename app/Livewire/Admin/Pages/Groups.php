<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\GroupForm;
use App\Models\Group;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Groups extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public GroupForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function create()
    {
        $this->authorize('access', 'admin.groups.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'group-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.groups.edit');

        $this->editId = $id;
        $group = Group::find($id);
        $this->form->setGroup($group);
        $this->dispatch('open-modal', 'group-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.groups.edit');

            $this->form->update();
            $this->editId = null;
            flash()->success('Group updated successfully');
        } else {
            $this->authorize('access', 'admin.groups.create');

            $this->form->store();
            flash()->success('Group created successfully');
        }
        $this->dispatch('close-modal', 'group-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.groups.destroy');

        $this->deleteId = $id;
        $group = Group::find($id);
        $this->form->setGroup($group);
        $this->dispatch('open-modal', 'delete-group-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.groups.destroy');

        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            flash()->success('Group deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-group-confirmation');
    }


    public function render()
    {
        $this->authorize('access', 'admin.groups.index');

        $table_heads = ['#', 'Name', 'Actions'];

        $groups = Group::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);

        return view('livewire.admin.pages.groups', [
            'groups' => $groups,
            'table_heads' => $table_heads,
        ]);
    }
}
