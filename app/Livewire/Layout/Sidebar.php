<?php

namespace App\Livewire\Layout;

use App\Models\CustomPermission;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Sidebar extends Component
{
    #[On('menuUpdated')]
    public function render()
    {
        $menus = Menu::where('is_active', true)
            ->orderBy('sort')
            ->get()
            ->filter(function ($menu) {
                $permission = CustomPermission::where('route_name', $menu->route_name)->first();
                // dd(Auth::user()->can($permission->name));
                // Jika tidak ada permission terkait atau user memiliki permission
                if ($permission) {
                    return Auth::user()->can($permission->name);
                } else {
                    return false;
                }
            })
            ->groupBy('main_menu');

        return view('livewire.layout.sidebar', compact('menus'));
    }
}
