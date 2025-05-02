<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\MenuForm;
use App\Models\Menu as ModelsMenu;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Menu extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public MenuForm $form;
    public $search = '';
    public $routes = [];
    public $editId = null;
    public $deleteId = null;
    public $icons = [];

    public function getHeroIcons()
    {
        $solidIconsPath = base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg');

        $solidIcons = File::files($solidIconsPath);

        $icons = [];

        // Process solid icons (s- prefix)
        foreach ($solidIcons as $file) {
            $name = str_replace('.svg', '', $file->getFilename());

            $name = str_replace('.svg', '', $file->getFilename());

            // Tambahkan filter untuk icon yang diawali dengan 'c-'
            if (Str::startsWith($name, 'c-')) {
                $displayName = ucwords(str_replace('-', ' ', $file->getFilenameWithoutExtension()));
                $icons[$name] = Str::replaceStart('C', '', $displayName);
            }
        }

        ksort($icons);

        return $icons;
    }

    public function mount()
    {
        $this->authorize('access', 'admin.menus.index');

        $this->icons = $this->getHeroIcons();
        $routes = Route::getRoutes();
        $routeNames = [];

        $excludedPrefixes = ['ignition.', 'sanctum.', 'generated::', 'livewire.', 'telescope.', 'horizon.', 'debugbar.', 'nova.'];

        foreach ($routes as $route) {
            $name = $route->getName();

            if ($name && !Str::startsWith($name, $excludedPrefixes)) {
                $routeNames[] = $name;
            }
        }
        $this->routes = $routeNames;
    }

    public function create()
    {
        $this->authorize('access', 'admin.menus.create');

        $this->form->reset();
        $this->dispatch('open-modal', 'menu-modal');
    }
    public function save()
    {
        if ($this->editId) {
            $this->authorize('access', 'admin.menus.edit');

            $this->form->update();
            $this->editId = null;
            toastr()->success('Menu updated successfully');
        } else {
            $this->authorize('access', 'admin.menus.create');

            $this->form->store();
            toastr()->success('Menu created successfully');
        }
        $this->dispatch('close-modal', 'menu-modal');
        $this->dispatch('menuUpdated');
    }

    public function edit($id)
    {
        $this->authorize('access', 'admin.menus.edit');

        $menu = ModelsMenu::find($id);
        if ($menu) {
            $this->editId = $id;
            $this->form->setDataMenu($menu);
            $this->dispatch('open-modal', 'menu-modal');
        } else {
            $this->dispatch('error', 'Menu not found');
        }
    }

    public function confirmDelete($id)
    {
        $this->authorize('access', 'admin.menus.destroy');

        $this->deleteId = $id;
        $menu = ModelsMenu::find($id);
        $this->form->setDataMenu($menu);
        $this->dispatch('open-modal', 'delete-menu-confirmation');
    }
    public function delete()
    {
        $this->authorize('access', 'admin.menus.destroy');

        $this->form->delete();
        $this->deleteId = null;
        toastr()->success('Menu deleted successfully');
        $this->dispatch('close-modal', 'delete-menu-confirmation');
        $this->dispatch('menuUpdated');
    }

    public function render()
    {
        $this->authorize('access', 'admin.menus.index');

        $table_heads = ['#', 'Main Menu', 'Menu', 'Route Name', 'Icon', 'Sort', 'Action'];

        $menus = ModelsMenu::when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('menu', 'like', '%' . $this->search . '%')
                    ->orWhere('main_menu', 'like', '%' . $this->search . '%')
                    ->orWhere('route_name', 'like', '%' . $this->search . '%');
            });
        })
            ->orderBy('sort')
            ->paginate(10);

        return view('livewire.admin.pages.menu', compact('table_heads', 'menus'));
    }
}
