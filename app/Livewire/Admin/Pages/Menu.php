<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\MenuForm;
use App\Models\Menu as ModelsMenu;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Menu extends Component
{
    use WithPagination;

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
        $this->form->reset();
        $this->dispatch('open-modal', 'menu-modal');
    }
    public function save()
    {
        if ($this->editId) {
            $this->form->update();
            $this->editId = null;
            $this->dispatch('updateSuccess');
        } else {
            $this->form->store();
            $this->dispatch('createSuccess');
        }
        $this->dispatch('close-modal', 'menu-modal');
        $this->dispatch('menuUpdated');
    }

    public function edit($id)
    {
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
        $this->deleteId = $id;
        $menu = ModelsMenu::find($id);
        $this->form->setDataMenu($menu);
        $this->dispatch('open-modal', 'delete-menu-confirmation');
    }
    public function delete()
    {
        $this->form->delete();
        $this->dispatch('deleteSuccess');
        $this->deleteId = null;
        $this->dispatch('close-modal', 'delete-menu-confirmation');
    }

    public function render()
    {
        $table_heads = ['#', 'Main Menu', 'Menu', 'Route Name', 'Icon', 'Sort', 'Action'];

        $menus = ModelsMenu::where('menu', 'like', '%' . $this->search . '%')
            ->orderBy('sort')
            ->paginate();

        return view('livewire.admin.pages.menu', compact('table_heads', 'menus'));
    }
}
