<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class UserObserver
{
    /**
     * Cache keys
     */
    private const SIDEBAR_CACHE_PREFIX = 'sidebar_menus_user_';

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::info('User created', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Clear sidebar cache for this user
        $this->clearUserSidebarCache($user->id);

        Log::info('User updated', [
            'id' => $user->id,
            'name' => $user->name,
            'changes' => $user->getChanges(),
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Clear sidebar cache for this user
        $this->clearUserSidebarCache($user->id);

        Log::info('User deleted', [
            'id' => $user->id,
            'name' => $user->name,
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Log::info('User restored', ['id' => $user->id]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Clear sidebar cache for this user
        $this->clearUserSidebarCache($user->id);

        Log::info('User force deleted', ['id' => $user->id]);
    }

    /**
     * Clear sidebar cache for a specific user.
     */
    private function clearUserSidebarCache(int $userId): void
    {
        try {
            Cache::forget(self::SIDEBAR_CACHE_PREFIX . $userId);
            Log::debug('Sidebar cache cleared for user', ['user_id' => $userId]);
        } catch (\Exception $e) {
            Log::error('Failed to clear user sidebar cache', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear all permission-related caches for a user.
     * Public so it can be called from other places (e.g., after syncPermissions).
     */
    public static function clearUserCaches(int $userId): void
    {
        try {
            // Clear Spatie permission cache
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            
            // Clear sidebar cache for this user
            Cache::forget(self::SIDEBAR_CACHE_PREFIX . $userId);

            Log::debug('User caches cleared', ['user_id' => $userId]);
        } catch (\Exception $e) {
            Log::error('Failed to clear user caches', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
