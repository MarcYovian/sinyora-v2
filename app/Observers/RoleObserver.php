<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleObserver
{
    /**
     * Cache keys
     */
    private const SIDEBAR_CACHE_PREFIX = 'sidebar_menus_user_';

    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        $this->clearAllCaches();
        Log::info('Role created', [
            'id' => $role->id,
            'name' => $role->name,
        ]);
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        $this->clearAllCaches();
        Log::info('Role updated', [
            'id' => $role->id,
            'name' => $role->name,
            'changes' => $role->getChanges(),
        ]);
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        $this->clearAllCaches();
        Log::info('Role deleted', [
            'id' => $role->id,
            'name' => $role->name,
        ]);
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        $this->clearAllCaches();
        Log::info('Role restored', ['id' => $role->id]);
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        $this->clearAllCaches();
        Log::info('Role force deleted', ['id' => $role->id]);
    }

    /**
     * Clear all permission-related caches.
     * This includes both Spatie permission cache and sidebar cache.
     * Public so it can be called from other places (e.g., after syncPermissions).
     */
    public static function clearAllCaches(): void
    {
        try {
            // Clear Spatie permission cache
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            
            Log::debug('Spatie permission cache cleared');
        } catch (\Exception $e) {
            Log::error('Failed to clear Spatie permission cache', [
                'error' => $e->getMessage(),
            ]);
        }

        try {
            // Clear sidebar cache for all users
            $userIds = User::pluck('id');

            foreach ($userIds as $userId) {
                Cache::forget(self::SIDEBAR_CACHE_PREFIX . $userId);
            }

            Log::debug('Sidebar caches cleared for all users', [
                'user_count' => $userIds->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear sidebar caches', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
