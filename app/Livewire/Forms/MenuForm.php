<?php

namespace App\Livewire\Forms;

use App\Models\Menu;
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

    public function store()
    {
        $validated = $this->validate();

        Menu::create($validated);

        $this->reset();
    }

    public function update()
    {
        $validated = $this->validate();

        $this->dataMenu->update($validated);

        $this->reset();
    }

    public function delete()
    {
        if ($this->dataMenu) {
            $this->dataMenu->delete();
            $this->reset();
        }
    }
}
