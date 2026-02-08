<?php

namespace App\Observers;

use App\Models\CustomPermission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class CustomPermissionObserver
{
    /**
     * Cache keys
     */
    private const SIDEBAR_CACHE_PREFIX = 'sidebar_menus_user_';

    /**
     * Handle the CustomPermission "created" event.
     */
    public function created(CustomPermission $permission): void
    {
        $this->clearAllCaches();
        Log::info('Permission created', [
            'id' => $permission->id,
            'name' => $permission->name,
            'route_name' => $permission->route_name,
        ]);
    }

    /**
     * Handle the CustomPermission "updated" event.
     */
    public function updated(CustomPermission $permission): void
    {
        $this->clearAllCaches();
        Log::info('Permission updated', [
            'id' => $permission->id,
            'name' => $permission->name,
            'changes' => $permission->getChanges(),
        ]);
    }

    /**
     * Handle the CustomPermission "deleted" event.
     */
    public function deleted(CustomPermission $permission): void
    {
        $this->clearAllCaches();
        Log::info('Permission deleted', [
            'id' => $permission->id,
            'name' => $permission->name,
        ]);
    }

    /**
     * Handle the CustomPermission "restored" event.
     */
    public function restored(CustomPermission $permission): void
    {
        $this->clearAllCaches();
        Log::info('Permission restored', ['id' => $permission->id]);
    }

    /**
     * Handle the CustomPermission "force deleted" event.
     */
    public function forceDeleted(CustomPermission $permission): void
    {
        $this->clearAllCaches();
        Log::info('Permission force deleted', ['id' => $permission->id]);
    }

    /**
     * Clear all permission-related caches.
     * This includes both Spatie permission cache and sidebar cache.
     */
    private function clearAllCaches(): void
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

            Log::debug('Sidebar caches cleared for all users due to permission change', [
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

