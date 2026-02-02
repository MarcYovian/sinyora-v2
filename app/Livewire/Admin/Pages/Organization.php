<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\OrganizationForm;
use App\Models\Organization as ModelsOrganization;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Organization extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public OrganizationForm $form;
    public $search = '';
    public $editId = null;
    public $deleteId = null;

    public function create()
    {
        $this->authorize('access', 'admin.organizations.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'organization-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.organizations.edit');

        $this->editId = $id;
        $organization = ModelsOrganization::find($id);
        $this->form->setOrganization($organization);
        $this->dispatch('open-modal', 'organization-modal');
    }

    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.organizations.edit');

            $this->form->update();
            $this->editId = null;
            flash()->success('Organization updated successfully');
        } else {
            $this->authorize('access', 'admin.organizations.create');

            $this->form->store();
            flash()->success('Organization created successfully');
        }
        $this->dispatch('close-modal', 'organization-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.organizations.destroy');

        $this->deleteId = $id;
        $organization = ModelsOrganization::find($id);
        $this->form->setOrganization($organization);
        $this->dispatch('open-modal', 'delete-organization-confirmation');
    }

    public function delete()
    {
        $this->authorize('access', 'admin.organizations.destroy');

        if ($this->deleteId) {
            $this->form->delete();
            $this->deleteId = null;
            flash()->success('Organization deleted successfully');
        }
        $this->dispatch('close-modal', 'delete-organization-confirmation');
    }

    public function render()
    {
        $this->authorize('access', 'admin.organizations.index');

        $table_heads = ['#', 'Name', 'Code', 'Description', 'Status', 'Actions'];

        $organizations = ModelsOrganization::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%');
        })->latest()->paginate(5);

        return view('livewire.admin.pages.organization', [
            'organizations' => $organizations,
            'table_heads' => $table_heads
        ]);
    }
}
