<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\UserForm;
use App\Models\Group;
use App\Models\User as UserModal;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class User extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]


    public UserForm $form;

    public $roles;

    public $search = '';
    public $searchPermission = '';
    public $groups;
    public $editId = null;
    public $deleteId = null;
    public $correlationId;

    // Check if search has no results
    public function hasNoResults()
    {
        if (empty($this->searchPermission)) return false;

        return Group::with(['permissions' => function ($query) {
            $query->where('name', 'like', '%' . $this->searchPermission . '%');
        }])->get()->flatMap->permissions->isEmpty();
    }

    public function save()
    {
        Log::info('User save action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.users.edit');

                $this->form->update();
                $this->dispatch('close-modal', 'user-modal');
                toastr()->success('User updated successfully.');

                // Log the successful update
                Log::info('User updated successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'input' => $this->form->except(['password', 'password_confirmation']),
                ]);
            } else {
                $this->authorize('access', 'admin.users.create');

                $this->form->store();
                $this->dispatch('close-modal', 'user-modal');
                toastr()->success('User created successfully.');

                // Log the successful creation
                Log::info('User created successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'input' => $this->form->except(['password', 'password_confirmation']),
                ]);
            }
            $this->dispatch('close-modal', 'user-modal');
            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
        } catch (ValidationException $e) {
            $this->setErrorBag($e->errors());
        } catch (Exception $e) {
            Log::error('Save failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            toastr()->error("An error occurred while saving the user. Please try again later. #{$this->correlationId}");
        }
    }

    public function create()
    {
        $this->authorize('access', 'admin.users.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'user-modal');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.users.edit');

        $this->editId = $id;
        $user = UserModal::find($id);
        $this->form->setUser($user);
        $this->dispatch('open-modal', 'user-modal');
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.users.destroy');

        $this->deleteId = $id;
        $user = UserModal::find($id);
        $this->form->setUser($user);
        $this->dispatch('open-modal', 'delete-user-confirmation');
    }
    public function delete()
    {
        $this->authorize('access', 'admin.users.destroy');

        Log::info('User deletion action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->form->destroy();
            toastr()->success('User deleted successfully.');

            // Log the successful deletion
            Log::info('User deleted successfully', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (Exception $e) {
            Log::error('User deletion failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            toastr()->error("An error occurred while deleting the user. Please try again later. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-user-confirmation');
            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    public function confirmResetPassword($id)
    {
        $this->authorize('access', 'password.reset');
        $this->editId = $id;
        $user = UserModal::find($id);
        $this->form->setUser($user);
        $this->dispatch('open-modal', 'reset-password-confirmation');
    }

    public function resetPassword()
    {
        $this->authorize('access', 'password.reset');

        Log::info('Password reset action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->form->resetPassword();
            toastr()->success('Password reset successfully.');

            // Log the successful password reset
            Log::info('User password reset successfully', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (Exception $e) {
            Log::error('Password reset failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            toastr()->error("An error occurred while resetting the password. Please try again later. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'reset-password-confirmation');
            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    public function permission($id)
    {
        $this->authorize('access', 'admin.users.role-permission');

        $this->form->setUser(UserModal::find($id));
        $this->form->assignPermission();

        $this->groups = Group::with(['permissions' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get(['id', 'name']);

        $this->dispatch('open-modal', 'permission-modal');
    }

    public function syncPermission()
    {
        $this->authorize('access', 'admin.users.role-permission');

        Log::info('Permission sync action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {

            $this->form->syncPermissions();
            toastr()->success('Permissions updated successfully.');
            $this->dispatch('close-modal', 'permission-modal');

            // Log the successful permission sync
            Log::info('User permissions updated successfully', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (Exception $e) {
            Log::error('Permission sync failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            toastr()->error("An error occurred while syncing permissions. Please try again later. #{$this->correlationId}");
        }
    }

    public function mount()
    {
        $this->authorize('access', 'admin.users.index');

        $this->correlationId = Str::uuid()->toString();
        $this->roles = Role::all();
    }

    public function render()
    {
        $this->authorize('access', 'admin.users.index');

        $table_heads = ['#', 'Name', 'Username', 'Email', 'Email Verified At', 'Actions'];

        $users = UserModal::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('username', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%');
        })->latest()->paginate(10);

        return view('livewire.admin.pages.user', compact('table_heads', 'users'));
    }
}
