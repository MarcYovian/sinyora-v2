<?php

namespace App\Observers;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MenuObserver
{
    /**
     * Cache keys
     */
    private const SIDEBAR_CACHE_PREFIX = 'sidebar_menus_user_';

    /**
     * Handle the Menu "created" event.
     */
    public function created(Menu $menu): void
    {
        $this->clearAllSidebarCaches();
        Log::info('Menu created', [
            'id' => $menu->id,
            'menu' => $menu->menu,
            'route_name' => $menu->route_name,
        ]);
    }

    /**
     * Handle the Menu "updated" event.
     */
    public function updated(Menu $menu): void
    {
        $this->clearAllSidebarCaches();
        Log::info('Menu updated', [
            'id' => $menu->id,
            'menu' => $menu->menu,
            'changes' => $menu->getChanges(),
        ]);
    }

    /**
     * Handle the Menu "deleted" event.
     */
    public function deleted(Menu $menu): void
    {
        $this->clearAllSidebarCaches();
        Log::info('Menu deleted', [
            'id' => $menu->id,
            'menu' => $menu->menu,
        ]);
    }

    /**
     * Handle the Menu "restored" event.
     */
    public function restored(Menu $menu): void
    {
        $this->clearAllSidebarCaches();
        Log::info('Menu restored', ['id' => $menu->id]);
    }

    /**
     * Handle the Menu "force deleted" event.
     */
    public function forceDeleted(Menu $menu): void
    {
        $this->clearAllSidebarCaches();
        Log::info('Menu force deleted', ['id' => $menu->id]);
    }

    /**
     * Clear all sidebar caches for all users.
     * Called when menu data changes to ensure sidebar refreshes.
     */
    private function clearAllSidebarCaches(): void
    {
        try {
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
