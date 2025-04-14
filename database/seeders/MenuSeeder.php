<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::create([
            'main_menu' => 'Dashboard',
            'menu' => 'Dashboard',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.dashboard.index',
            'sort' => 1,
        ]);
        Menu::create([
            'main_menu' => 'Users',
            'menu' => 'Users',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.users.index',
            'sort' => 2,
        ]);
        Menu::create([
            'main_menu' => 'Roles Permissions',
            'menu' => 'Roles',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.roles.index',
            'sort' => 3,
        ]);
        Menu::create([
            'main_menu' => 'Roles Permissions',
            'menu' => 'Permissions',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.permissions.index',
            'sort' => 4,
        ]);
        Menu::create([
            'main_menu' => 'Settings',
            'menu' => 'Menu',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.menu.index',
            'sort' => 5,
        ]);
        Menu::create([
            'main_menu' => 'Masters',
            'menu' => 'Groups',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.groups.index',
            'sort' => 6,
        ]);
        Menu::create([
            'main_menu' => 'Masters',
            'menu' => 'Organizations',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.organizations.index',
            'sort' => 7,
        ]);
        Menu::create([
            'main_menu' => 'Masters',
            'menu' => 'Event Category',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.event-category.index',
            'sort' => 8,
        ]);
        menu::create([
            'main_menu' => 'Masters',
            'menu' => 'Locations',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.locations.index',
            'sort' => 9,
        ]);
        Menu::create([
            'main_menu' => 'Masters',
            'menu' => 'Events',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.events.index',
            'sort' => 10,
        ]);
    }
}
