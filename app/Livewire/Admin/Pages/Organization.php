<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\OrganizationForm;
use App\Models\Organization as ModelsOrganization;
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

class Organization extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public OrganizationForm $form;

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $editId = null;
    public ?int $deleteId = null;
    public string $correlationId = '';

    public array $table_heads = ['No', 'Name', 'Code', 'Description', 'Status', 'Actions'];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open create modal.
     */
    public function create(): void
    {
        $this->authorize('access', 'admin.organizations.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'organization-modal');
    }

    /**
     * Open edit modal for an organization.
     */
    public function edit(int $id): void
    {
        try {
            $this->authorize('access', 'admin.organizations.edit');

            $organization = ModelsOrganization::findOrFail($id);
            $this->editId = $id;
            $this->form->setOrganization($organization);
            $this->dispatch('open-modal', 'organization-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized organization edit attempt', ['organization_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('You are not authorized to edit this organization.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Organization not found for edit', ['organization_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Organization not found.');
        } catch (\Exception $e) {
            Log::error('Failed to load organization for edit', [
                'organization_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to load organization. Please try again.');
        }
    }

    /**
     * Save organization (create or update).
     */
    public function save(): void
    {
        Log::info('Organization save action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'edit_id' => $this->editId,
        ]);

        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.organizations.edit');

                $this->form->update();
                flash()->success('Organization updated successfully');

                Log::info('Organization updated successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'organization_id' => $this->editId,
                ]);
            } else {
                $this->authorize('access', 'admin.organizations.create');

                $this->form->store();
                flash()->success('Organization created successfully');

                Log::info('Organization created successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                ]);
            }

            $this->dispatch('close-modal', 'organization-modal');
            $this->editId = null;
            $this->deleteId = null;
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized organization save attempt', [
                'user_id' => Auth::id(),
                'action' => $this->editId ? 'edit' : 'create',
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Organization save failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while saving the organization. #{$this->correlationId}");
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        try {
            $this->authorize('access', 'admin.organizations.destroy');

            $organization = ModelsOrganization::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setOrganization($organization);
            $this->dispatch('open-modal', 'delete-organization-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized organization delete attempt', ['organization_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('You are not authorized to delete this organization.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Organization not found for delete', ['organization_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Organization not found.');
        }
    }

    /**
     * Delete an organization.
     */
    public function delete(): void
    {
        Log::info('Organization deletion action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'organization_id' => $this->deleteId,
        ]);

        try {
            $this->authorize('access', 'admin.organizations.destroy');

            if ($this->deleteId) {
                $organizationName = $this->form->name;
                $this->form->delete();

                flash()->success('Organization deleted successfully');
                Log::info('Organization deleted successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'deleted_organization_name' => $organizationName,
                ]);
            }
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized organization deletion', [
                'user_id' => Auth::id(),
                'delete_id' => $this->deleteId,
            ]);
            flash()->error('You are not authorized to delete this organization.');
        } catch (\Exception $e) {
            Log::error('Organization deletion failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Failed to delete organization. Please try again. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-organization-confirmation');
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    /**
     * Reset all filters and pagination.
     */
    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.organizations.index');

        $organizations = ModelsOrganization::query()
            ->select(['id', 'name', 'code', 'description', 'is_active', 'created_at'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(5);

        return view('livewire.admin.pages.organization', [
            'organizations' => $organizations,
            'table_heads' => $this->table_heads,
        ]);
    }
}
