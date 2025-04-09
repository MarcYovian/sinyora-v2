<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\GroupForm;
use App\Models\Group;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Groups extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    public GroupForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function create()
    {
        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'group-modal');
    }

    public function edit($id)
    {
        $this->editId = $id;
        $group = Group::find($id);
        $this->form->setGroup($group);
        $this->dispatch('open-modal', 'group-modal');
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
        $this->dispatch('close-modal', 'group-modal');
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $group = Group::find($id);
        $this->form->setGroup($group);
        $this->dispatch('open-modal', 'delete-group-confirmation');
    }

    public function delete()
    {
        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            $this->dispatch('deleteSuccess');
        }
        $this->dispatch('close-modal', 'delete-group-confirmation');
    }


    public function render()
    {
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
