<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\PermissionForm;
use App\Models\CustomPermission;
use App\Models\Group;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Permission extends Component
{
    use WithPagination, AuthorizesRequests;
    
    #[Layout('layouts.app')]

    public PermissionForm $form;

    #[Url(as: 'q')]
    public $search = '';

    public $editId = null;
    public $deleteId = null;

    public $groups;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->authorize('access', 'admin.permissions.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'permission-modal');
    }

    public function edit($id)
    {
        try {
            $this->authorize('access', 'admin.permissions.edit');

            $permission = CustomPermission::findOrFail($id);
            $this->editId = $id;
            $this->form->setPermission($permission);
            $this->dispatch('open-modal', 'permission-modal');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Permission not found for edit', ['permission_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Permission not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized permission edit attempt', ['permission_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to edit this permission.');
        } catch (\Exception $e) {
            Log::error('Failed to load permission for edit', [
                'permission_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to load permission. Please try again.');
        }
    }

    public function save()
    {
        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.permissions.edit');

                $this->form->update();
                $this->editId = null;
                flash()->success('Permission updated successfully');
                Log::info('Permission updated via Livewire', ['user_id' => auth()->id()]);
            } else {
                $this->authorize('access', 'admin.permissions.create');

                $this->form->store();
                flash()->success('Permission created successfully');
                Log::info('Permission created via Livewire', ['user_id' => auth()->id()]);
            }
            $this->dispatch('close-modal', 'permission-modal');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized permission save attempt', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (\Exception $e) {
            Log::error('Failed to save permission', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to save permission. Please try again.');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $this->authorize('access', 'admin.permissions.destroy');

            $permission = CustomPermission::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setPermission($permission);
            $this->dispatch('open-modal', 'delete-permission-confirmation');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Permission not found for delete confirmation', ['permission_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Permission not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized permission delete attempt', ['permission_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to delete this permission.');
        } catch (\Exception $e) {
            Log::error('Failed to prepare permission deletion', [
                'permission_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to prepare deletion. Please try again.');
        }
    }

    public function delete()
    {
        try {
            $this->authorize('access', 'admin.permissions.destroy');

            if ($this->deleteId) {
                $permissionName = $this->form->name;
                $this->form->destroy();
                $this->deleteId = null;

                flash()->success('Permission deleted successfully');
                Log::info('Permission deleted via Livewire', [
                    'permission_name' => $permissionName,
                    'user_id' => auth()->id(),
                ]);
            }
            $this->dispatch('close-modal', 'delete-permission-confirmation');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized permission delete attempt', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
            ]);
            flash()->error('You are not authorized to delete this permission.');
        } catch (\Exception $e) {
            Log::error('Failed to delete permission', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to delete permission. Please try again.');
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    public function mount()
    {
        $this->authorize('access', 'admin.permissions.index');

        $this->groups = Group::all();
    }

    public function render()
    {
        $this->authorize('access', 'admin.permissions.index');

        $table_heads = ['No', 'Group', 'Name', 'Route Name', 'Default', 'Actions'];

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
