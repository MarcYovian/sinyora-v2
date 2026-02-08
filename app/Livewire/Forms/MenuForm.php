<?php

namespace App\Livewire\Forms;

use App\Models\Menu;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Form;

class MenuForm extends Form
{
    public ?Menu $dataMenu;

    #[Validate('required')]
    public string $main_menu = '';

    #[Validate('required')]
    public string $menu = '';

    #[Validate('required')]
    public string $route_name = '';

    #[Validate('required')]
    public string $icon = '';

    #[Validate('required|numeric')]
    public int $sort = 0;

    public function setDataMenu(?Menu $menu): void
    {
        $this->dataMenu = $menu;
        $this->main_menu = $menu->main_menu;
        $this->menu = $menu->menu;
        $this->route_name = $menu->route_name;
        $this->icon = $menu->icon;
        $this->sort = $menu->sort;
    }

    public function store(): Menu
    {
        $validated = $this->validate();
        try {
            $menu = Menu::create($validated);

            Log::info('Menu created via form', [
                'menu_id' => $menu->id,
                'menu_name' => $menu->menu,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return $menu;
        } catch (\Exception $e) {
            Log::error('Failed to create menu', [
                'user_id' => auth()->id(),
                'data' => $validated,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function update(): Menu
    {
        $validated = $this->validate();

        try {
            $this->dataMenu->update($validated);

            Log::info('Menu updated via form', [
                'menu_id' => $this->dataMenu->id,
                'menu_name' => $this->dataMenu->menu,
                'changes' => $this->dataMenu->getChanges(),
                'user_id' => auth()->id(),
            ]);

            $menu = $this->dataMenu;
            $this->reset();

            return $menu;
        } catch (\Exception $e) {
            Log::error('Failed to update menu', [
                'menu_id' => $this->dataMenu?->id,
                'user_id' => auth()->id(),
                'data' => $validated,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function delete(): bool
    {
        if (!$this->dataMenu) {
            Log::warning('Delete attempt with no menu set', ['user_id' => auth()->id()]);
            return false;
        }

        try {
            $menuId = $this->dataMenu->id;
            $menuName = $this->dataMenu->menu;

            $this->dataMenu->delete();

            Log::info('Menu deleted via form', [
                'menu_id' => $menuId,
                'menu_name' => $menuName,
                'user_id' => auth()->id(),
            ]);

            $this->reset();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete menu', [
                'menu_id' => $this->dataMenu?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}

