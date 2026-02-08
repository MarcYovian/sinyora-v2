<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\OrganizationForm;
use App\Models\Organization as ModelsOrganization;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Organization extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public OrganizationForm $form;

    #[Url(as: 'q')]
    public $search = '';

    public $editId = null;
    public $deleteId = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

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
        try {
            $this->authorize('access', 'admin.organizations.edit');

            $organization = ModelsOrganization::findOrFail($id);
            $this->editId = $id;
            $this->form->setOrganization($organization);
            $this->dispatch('open-modal', 'organization-modal');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Organization not found for edit', ['organization_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Organization not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized organization edit attempt', ['organization_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to edit this organization.');
        } catch (\Exception $e) {
            Log::error('Failed to load organization for edit', [
                'organization_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to load organization. Please try again.');
        }
    }

    public function save()
    {
        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.organizations.edit');

                $this->form->update();
                $this->editId = null;
                flash()->success('Organization updated successfully');
                Log::info('Organization updated via Livewire', ['user_id' => auth()->id()]);
            } else {
                $this->authorize('access', 'admin.organizations.create');

                $this->form->store();
                flash()->success('Organization created successfully');
                Log::info('Organization created via Livewire', ['user_id' => auth()->id()]);
            }
            $this->dispatch('close-modal', 'organization-modal');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized organization save attempt', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (\Exception $e) {
            Log::error('Failed to save organization', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to save organization. Please try again.');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $this->authorize('access', 'admin.organizations.destroy');

            $organization = ModelsOrganization::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setOrganization($organization);
            $this->dispatch('open-modal', 'delete-organization-confirmation');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Organization not found for delete confirmation', ['organization_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Organization not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized organization delete attempt', ['organization_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to delete this organization.');
        } catch (\Exception $e) {
            Log::error('Failed to prepare organization deletion', [
                'organization_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to prepare deletion. Please try again.');
        }
    }

    public function delete()
    {
        try {
            $this->authorize('access', 'admin.organizations.destroy');

            if ($this->deleteId) {
                $organizationName = $this->form->name;
                $this->form->delete();
                $this->deleteId = null;

                flash()->success('Organization deleted successfully');
                Log::info('Organization deleted via Livewire', [
                    'organization_name' => $organizationName,
                    'user_id' => auth()->id(),
                ]);
            }
            $this->dispatch('close-modal', 'delete-organization-confirmation');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized organization delete attempt', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
            ]);
            flash()->error('You are not authorized to delete this organization.');
        } catch (\Exception $e) {
            Log::error('Failed to delete organization', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to delete organization. Please try again.');
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('access', 'admin.organizations.index');

        $table_heads = ['No', 'Name', 'Code', 'Description', 'Status', 'Actions'];

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
