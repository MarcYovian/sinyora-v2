<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\UserForm;
use App\Models\Group;
use App\Models\User as UserModel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class User extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public UserForm $form;

    public $roles;

    #[Url(as: 'q')]
    public $search = '';

    public $searchPermission = '';
    public $groups;

    public $editId = null;
    public $deleteId = null;
    public $correlationId;

    public $table_heads = ['No', 'Name', 'Username', 'Email', 'Email Verified At', 'Actions'];

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
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
     * Check if permission search has no results.
     */
    public function hasNoResults(): bool
    {
        if (empty($this->searchPermission)) {
            return false;
        }

        return Group::with(['permissions' => function ($query) {
            $query->where('name', 'like', '%' . $this->searchPermission . '%');
        }])->get()->flatMap->permissions->isEmpty();
    }

    /**
     * Open create modal.
     */
    public function create(): void
    {
        try {
            $this->authorize('access', 'admin.users.create');

            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
            $this->dispatch('open-modal', 'user-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized user create attempt', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to create users.');
        }
    }

    /**
     * Save user (create or update).
     */
    public function save(): void
    {
        Log::info('User save action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.users.edit');

                $this->form->update();
                flash()->success('User updated successfully.');

                Log::info('User updated successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'updated_user_id' => $this->editId,
                ]);
            } else {
                $this->authorize('access', 'admin.users.create');

                $this->form->store();
                flash()->success('User created successfully.');

                Log::info('User created successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                ]);
            }

            $this->dispatch('close-modal', 'user-modal');
            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized user save attempt', [
                'user_id' => Auth::id(),
                'action' => $this->editId ? 'edit' : 'create',
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (ValidationException $e) {
            $this->setErrorBag($e->errors());
        } catch (\Exception $e) {
            Log::error('User save failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while saving the user. Please try again later. #{$this->correlationId}");
        }
    }

    /**
     * Open edit modal for a user.
     */
    public function edit(int $id): void
    {
        try {
            $this->authorize('access', 'admin.users.edit');

            $user = UserModel::findOrFail($id);
            $this->editId = $id;
            $this->form->setUser($user);
            $this->dispatch('open-modal', 'user-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized user edit attempt', ['user_id' => Auth::id(), 'target_user_id' => $id]);
            flash()->error('You are not authorized to edit users.');
        } catch (ModelNotFoundException $e) {
            Log::warning('User not found for edit', ['target_user_id' => $id]);
            flash()->error('User not found.');
        } catch (\Exception $e) {
            Log::error('Failed to load user for edit', [
                'target_user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to load user. Please try again.');
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        try {
            $this->authorize('access', 'admin.users.destroy');

            $user = UserModel::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setUser($user);
            $this->dispatch('open-modal', 'delete-user-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized user delete attempt', ['user_id' => Auth::id(), 'target_user_id' => $id]);
            flash()->error('You are not authorized to delete users.');
        } catch (ModelNotFoundException $e) {
            Log::warning('User not found for delete', ['target_user_id' => $id]);
            flash()->error('User not found.');
        }
    }

    /**
     * Delete a user.
     */
    public function delete(): void
    {
        Log::info('User deletion action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'target_user_id' => $this->deleteId,
        ]);

        try {
            $this->authorize('access', 'admin.users.destroy');

            $this->form->destroy();
            flash()->success('User deleted successfully.');

            Log::info('User deleted successfully', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'deleted_user_id' => $this->deleteId,
            ]);
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized user deletion', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to delete users.');
        } catch (\Exception $e) {
            Log::error('User deletion failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while deleting the user. Please try again later. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-user-confirmation');
            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    /**
     * Open reset password confirmation modal.
     */
    public function confirmResetPassword(int $id): void
    {
        try {
            $this->authorize('access', 'password.reset');

            $user = UserModel::findOrFail($id);
            $this->editId = $id;
            $this->form->setUser($user);
            $this->dispatch('open-modal', 'reset-password-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized password reset attempt', ['user_id' => Auth::id(), 'target_user_id' => $id]);
            flash()->error('You are not authorized to reset passwords.');
        } catch (ModelNotFoundException $e) {
            Log::warning('User not found for password reset', ['target_user_id' => $id]);
            flash()->error('User not found.');
        }
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(): void
    {
        Log::info('Password reset action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'target_user_id' => $this->editId,
        ]);

        try {
            $this->authorize('access', 'password.reset');

            $this->form->resetPassword();
            flash()->success('Password reset successfully.');

            Log::info('User password reset successfully', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'target_user_id' => $this->editId,
            ]);
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized password reset', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to reset passwords.');
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while resetting the password. Please try again later. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'reset-password-confirmation');
            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    /**
     * Open permission management modal.
     */
    public function permission(int $id): void
    {
        try {
            $this->authorize('access', 'admin.users.role-permission');

            $user = UserModel::findOrFail($id);
            $this->form->setUser($user);
            $this->form->assignPermission();

            $this->groups = Group::with(['permissions' => function ($query) {
                $query->orderBy('name');
            }])->orderBy('name')->get(['id', 'name']);

            $this->dispatch('open-modal', 'permission-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized permission management attempt', ['user_id' => Auth::id(), 'target_user_id' => $id]);
            flash()->error('You are not authorized to manage permissions.');
        } catch (ModelNotFoundException $e) {
            Log::warning('User not found for permission management', ['target_user_id' => $id]);
            flash()->error('User not found.');
        } catch (\Exception $e) {
            Log::error('Failed to load user permissions', [
                'target_user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to load permissions. Please try again.');
        }
    }

    /**
     * Sync user permissions.
     */
    public function syncPermission(): void
    {
        Log::info('Permission sync action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'target_user_id' => $this->form->user?->id,
        ]);

        try {
            $this->authorize('access', 'admin.users.role-permission');

            $this->form->syncPermissions();

            // Dispatch event to refresh sidebar in real-time
            $this->dispatch('menuUpdated');

            flash()->success('Permissions updated successfully.');

            Log::info('User permissions updated successfully', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'target_user_id' => $this->form->user?->id,
            ]);

            $this->dispatch('close-modal', 'permission-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized permission sync', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to modify permissions.');
        } catch (\Exception $e) {
            Log::error('Permission sync failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while syncing permissions. Please try again later. #{$this->correlationId}");
        }
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('access', 'admin.users.index');

        $this->correlationId = Str::uuid()->toString();
        $this->roles = Cache::remember('roles_dropdown', 3600, function () {
            return Role::all(['id', 'name']);
        });
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.users.index');

        $users = UserModel::query()
            ->select(['id', 'name', 'username', 'email', 'email_verified_at', 'created_at'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })->latest()->paginate(10);

        return view('livewire.admin.pages.user', [
            'table_heads' => $this->table_heads,
            'users' => $users,
        ]);
    }
}
