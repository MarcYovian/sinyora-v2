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
        Menu::truncate();

        Menu::create([
            'main_menu' => 'Dashboard',
            'menu' => 'Dashboard',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.dashboard.index',
            'sort' => 1,
        ]);
        Menu::create([
            'main_menu' => 'Articles',
            'menu' => 'Articles',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.articles.index',
            'sort' => 2,
        ]);
        Menu::create([
            'main_menu' => 'Articles',
            'menu' => 'Categories',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.articles.categories.index',
            'sort' => 3,
        ]);
        Menu::create([
            'main_menu' => 'Articles',
            'menu' => 'Tags',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.articles.tags.index',
            'sort' => 4,
        ]);
        Menu::create([
            'main_menu' => 'Events',
            'menu' => 'Events',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.events.index',
            'sort' => 5,
        ]);
        Menu::create([
            'main_menu' => 'Events',
            'menu' => 'Categories',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.event-categories.index',
            'sort' => 6,
        ]);
        Menu::create([
            'main_menu' => 'Borrowings',
            'menu' => 'Borrowings',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.asset-borrowings.index',
            'sort' => 7,
        ]);
        Menu::create([
            'main_menu' => 'Borrowings',
            'menu' => 'Assets',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.assets.index',
            'sort' => 8,
        ]);
        Menu::create([
            'main_menu' => 'Borrowings',
            'menu' => 'Asset Categories',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.asset-categories.index',
            'sort' => 9,
        ]);
        Menu::create([
            'main_menu' => 'Users',
            'menu' => 'Users',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.users.index',
            'sort' => 10,
        ]);
        Menu::create([
            'main_menu' => 'Users',
            'menu' => 'Roles',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.roles.index',
            'sort' => 11,
        ]);
        Menu::create([
            'main_menu' => 'Users',
            'menu' => 'Permissions',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.permissions.index',
            'sort' => 12,
        ]);
        Menu::create([
            'main_menu' => 'Settings',
            'menu' => 'Menus',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.menus.index',
            'sort' => 13,
        ]);
        Menu::create([
            'main_menu' => 'Documents',
            'menu' => 'Documents',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.documents.index',
            'sort' => 14,
        ]);
        Menu::create([
            'main_menu' => 'Masters',
            'menu' => 'Groups',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.groups.index',
            'sort' => 15,
        ]);
        Menu::create([
            'main_menu' => 'Masters',
            'menu' => 'Organizations',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.organizations.index',
            'sort' => 16,
        ]);
        menu::create([
            'main_menu' => 'Masters',
            'menu' => 'Locations',
            'icon' => 'c-academic-cap',
            'route_name' => 'admin.locations.index',
            'sort' => 17,
        ]);

        // Content Settings Menus
        Menu::create([
            'main_menu' => 'Content Settings',
            'menu' => 'Home Content',
            'icon' => 'c-home',
            'route_name' => 'admin.content.home',
            'sort' => 18,
        ]);
        Menu::create([
            'main_menu' => 'Content Settings',
            'menu' => 'Mass Schedules',
            'icon' => 'c-calendar',
            'route_name' => 'admin.content.mass-schedules',
            'sort' => 19,
        ]);
    }
}
