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
            'icon' => 'home',
            'route_name' => 'admin.dashboard.index',
            'sort' => 1,
        ]);
        Menu::create([
            'main_menu' => 'Users',
            'menu' => 'Users',
            'icon' => 'users',
            'route_name' => 'admin.users.index',
            'sort' => 2,
        ]);
        Menu::create([
            'main_menu' => 'Roles Permissions',
            'menu' => 'Roles',
            'icon' => 'shield',
            'route_name' => 'admin.roles.index',
            'sort' => 3,
        ]);
        Menu::create([
            'main_menu' => 'Roles Permissions',
            'menu' => 'Permissions',
            'icon' => 'lock',
            'route_name' => 'admin.permissions.index',
            'sort' => 4,
        ]);
        Menu::create([
            'main_menu' => 'Settings',
            'menu' => 'Menu',
            'icon' => 'list',
            'route_name' => 'admin.menu.index',
            'sort' => 5,
        ]);
        Menu::create([
            'main_menu' => 'Masters',
            'menu' => 'Groups',
            'icon' => 'group',
            'route_name' => 'admin.groups.index',
            'sort' => 6,
        ]);
    }
}
