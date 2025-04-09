<?php

namespace App\Livewire\Forms;

use App\Models\PublicMenu;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PublicMenuForm extends Form
{
    public ?PublicMenu $publicMenu;

    #[Validate('required')]
    public string $main_menu = '';

    #[Validate('required')]
    public string $menu = '';

    #[Validate('required')]
    public string $link = '';
    #[Validate('required')]
    public string $link_type = '';
    #[Validate('nullable')]
    public string $link_anchor = '';
    #[Validate('required')]
    public string $open_in_new_tab = '';

    #[Validate('nullable')]
    public string $icon = '';

    #[Validate('required|boolean')]
    public string $is_active = '';

    #[Validate('required|numeric')]
    public int $sort = 0;

    public $use_anchor = false;

    public function setDataMenu(?PublicMenu $publicMenu): void
    {
        $this->publicMenu = $publicMenu;
        $this->main_menu = $publicMenu->main_menu;
        $this->menu = $publicMenu->menu;
        $this->link = $publicMenu->link;
        $this->link_type = $publicMenu->link_type;
        $this->link_anchor = $publicMenu->link_anchor;
        $this->open_in_new_tab = $publicMenu->open_in_new_tab;
        $this->icon = $publicMenu->icon;
        $this->is_active = $publicMenu->is_active;
        $this->sort = $publicMenu->sort;
    }

    public function store()
    {
        $validated = $this->validate();

        PublicMenu::create($validated);

        $this->reset();
    }

    public function update()
    {
        $validated = $this->validate();

        $this->publicMenu->update($validated);

        $this->reset();
    }

    public function delete()
    {
        if ($this->publicMenu) {
            $this->publicMenu->delete();
            $this->reset();
        }
    }
}
