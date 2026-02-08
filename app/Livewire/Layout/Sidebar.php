<?php

namespace App\Livewire\Layout;

use App\Models\CustomPermission;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class Sidebar extends Component
{
    #[On('menuUpdated')]
    public function refreshMenu()
    {
        // Invalidate cache when menu is updated
        $userId = Auth::id();
        Cache::forget("sidebar_menus_user_{$userId}");
    }

    public function render()
    {
        $userId = Auth::id();
        $cacheKey = "sidebar_menus_user_{$userId}";

        // Cache menu selama 1 jam (3600 detik)
        $menus = Cache::remember($cacheKey, 3600, function () {
            // Ambil semua menu aktif
            $allMenus = Menu::where('is_active', true)
                ->orderBy('sort')
                ->get();

            // Ambil semua route_name dari menu
            $routeNames = $allMenus->pluck('route_name')->toArray();

            // Single query: ambil semua permission yang terkait dengan menu
            $permissions = CustomPermission::whereIn('route_name', $routeNames)
                ->get()
                ->keyBy('route_name');

            // Filter menu berdasarkan permission user
            return $allMenus->filter(function ($menu) use ($permissions) {
                $permission = $permissions->get($menu->route_name);

                if ($permission) {
                    return Auth::user()->can($permission->name);
                }

                return false;
            })->groupBy('main_menu');
        });

        return view('livewire.layout.sidebar', compact('menus'));
    }
}
