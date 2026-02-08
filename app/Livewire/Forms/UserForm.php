<?php

namespace App\Livewire\Forms;

use App\Models\CustomPermission;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user = null;

    public array $rolePermissions = [];
    public array $directPermissions = [];
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $role = '';
    public bool $assignDefaultPermissions = true;

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($this->user?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user?->id)],
            'role' => ['required', 'string', 'max:255', Rule::exists('roles', 'name')],
        ];
    }

    /**
     * Set the user instance for editing.
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;

        if ($user) {
            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->role = $user->roles->isNotEmpty() ? $user->roles->first()->name : '';
        }
    }

    /**
     * Create a new user.
     */
    public function store(): User
    {
        $this->validate();

        try {
            $user = User::create([
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'password' => Hash::make('password'),
            ]);

            $user->assignRole($this->role);

            // Only assign default permissions if checkbox is checked
            if ($this->assignDefaultPermissions) {
                $defaultPermissions = CustomPermission::where('default', 'Default')
                    ->pluck('name')
                    ->toArray();
                
                if (!empty($defaultPermissions)) {
                    $user->givePermissionTo($defaultPermissions);
                    Log::info('Default permissions assigned to new user', [
                        'user_id' => $user->id,
                        'permission_count' => count($defaultPermissions),
                    ]);
                }
            }

            Log::info('User created via admin form', [
                'user_id' => $user->id,
                'name' => $user->name,
                'assign_default' => $this->assignDefaultPermissions,
                'created_by' => auth()->id(),
            ]);

            $this->reset();

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'name' => $this->name,
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing user.
     */
    public function update(): User
    {
        $this->validate();

        if (!$this->user) {
            throw new \RuntimeException('No user set for update');
        }

        try {
            $this->user->update([
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
            ]);

            $this->user->syncRoles([$this->role]);

            Log::info('User updated via admin form', [
                'user_id' => $this->user->id,
                'updated_by' => auth()->id(),
            ]);

            $updatedUser = $this->user;
            $this->reset();

            return $updatedUser;
        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete the user.
     */
    public function destroy(): void
    {
        if (!$this->user) {
            throw new \RuntimeException('No user set for deletion');
        }

        try {
            $userId = $this->user->id;
            $userName = $this->user->name;

            // Detach roles and permissions before deletion
            $this->user->roles()->detach();
            $this->user->permissions()->detach();
            $this->user->delete();

            Log::info('User deleted via admin form', [
                'deleted_user_id' => $userId,
                'deleted_user_name' => $userName,
                'deleted_by' => auth()->id(),
            ]);

            $this->reset();
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reset the user's password to default.
     */
    public function resetPassword(): void
    {
        if (!$this->user) {
            throw new \RuntimeException('No user set for password reset');
        }

        try {
            $this->user->update([
                'password' => Hash::make('password'),
            ]);

            Log::info('User password reset via admin', [
                'user_id' => $this->user->id,
                'reset_by' => auth()->id(),
            ]);

            $this->reset(['name', 'email']);
        } catch (\Exception $e) {
            Log::error('Failed to reset user password', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Load permissions for the user (role and direct).
     */
    public function assignPermission(): void
    {
        if (!$this->user) {
            return;
        }

        $this->rolePermissions = $this->user->getPermissionsViaRoles()->pluck('name')->toArray();
        $this->directPermissions = $this->user->getDirectPermissions()->pluck('name')->toArray();
    }

    /**
     * Sync direct permissions for the user.
     */
    public function syncPermissions(): void
    {
        if (!$this->user) {
            throw new \RuntimeException('No user set for permission sync');
        }

        try {
            $this->user->syncPermissions($this->directPermissions);

            // Clear caches for the user
            UserObserver::clearUserCaches($this->user->id);

            Log::info('User permissions synced', [
                'user_id' => $this->user->id,
                'permission_count' => count($this->directPermissions),
                'synced_by' => auth()->id(),
            ]);

            $this->reset();
        } catch (\Exception $e) {
            Log::error('Failed to sync user permissions', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
