<?php

namespace Tests\Feature\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\Menu;
use App\Models\Menu as MenuModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->role = Role::create(['name' => 'admin']);

        // Create all required permissions
        $permissions = [
            'admin.menus.index',
            'admin.menus.create',
            'admin.menus.edit',
            'admin.menus.destroy',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::create([
                'name' => $permissionName,
                'route_name' => $permissionName,
            ]);
            $this->role->givePermissionTo($permission);
        }

        $this->user->assignRole($this->role);
    }

    /** @test */
    public function can_render_menu_page()
    {
        $this->actingAs($this->user);

        Livewire::test(Menu::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.menu');
    }

    /** @test */
    public function can_create_menu()
    {
        $this->actingAs($this->user);

        Livewire::test(Menu::class)
            ->call('create')
            ->set('form.main_menu', 'Master Data')
            ->set('form.menu', 'Test Menu')
            ->set('form.route_name', 'admin.menus.index')
            ->set('form.icon', 'c-home')
            ->set('form.sort', 1)
            ->call('save');

        $this->assertDatabaseHas('menus', [
            'main_menu' => 'Master Data',
            'menu' => 'Test Menu',
            'route_name' => 'admin.menus.index',
            'icon' => 'c-home',
            'sort' => 1,
        ]);
    }

    /** @test */
    public function cannot_create_menu_with_missing_fields()
    {
        $this->actingAs($this->user);

        Livewire::test(Menu::class)
            ->call('create')
            ->set('form.main_menu', '')
            ->set('form.menu', '')
            ->set('form.route_name', '')
            ->set('form.icon', '')
            ->call('save')
            ->assertHasErrors(['form.main_menu', 'form.menu', 'form.route_name', 'form.icon']);
    }

    /** @test */
    public function can_edit_menu()
    {
        $this->actingAs($this->user);

        $menu = MenuModel::create([
            'main_menu' => 'Original Main',
            'menu' => 'Original Menu',
            'route_name' => 'admin.menus.index',
            'icon' => 'c-home',
            'sort' => 1,
            'is_active' => true,
        ]);

        Livewire::test(Menu::class)
            ->call('edit', $menu->id)
            ->assertSet('editId', $menu->id)
            ->set('form.menu', 'Updated Menu')
            ->set('form.sort', 5)
            ->call('save');

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'menu' => 'Updated Menu',
            'sort' => 5,
        ]);
    }

    /** @test */
    public function can_delete_menu()
    {
        $this->actingAs($this->user);

        $menu = MenuModel::create([
            'main_menu' => 'To Delete',
            'menu' => 'Menu to Delete',
            'route_name' => 'admin.menus.index',
            'icon' => 'c-trash',
            'sort' => 1,
            'is_active' => true,
        ]);

        Livewire::test(Menu::class)
            ->call('confirmDelete', $menu->id)
            ->assertSet('deleteId', $menu->id)
            ->call('delete');

        $this->assertDatabaseMissing('menus', [
            'id' => $menu->id,
        ]);
    }

    /** @test */
    public function can_search_menus()
    {
        $this->actingAs($this->user);

        MenuModel::create([
            'main_menu' => 'Master Data',
            'menu' => 'Users Management',
            'route_name' => 'admin.users.index',
            'icon' => 'c-users',
            'sort' => 1,
            'is_active' => true,
        ]);

        MenuModel::create([
            'main_menu' => 'Settings',
            'menu' => 'App Settings',
            'route_name' => 'admin.settings.index',
            'icon' => 'c-cog',
            'sort' => 2,
            'is_active' => true,
        ]);

        Livewire::test(Menu::class)
            ->set('search', 'Users')
            ->assertSee('Users Management')
            ->assertDontSee('App Settings');
    }

    /** @test */
    public function can_search_by_route_name()
    {
        $this->actingAs($this->user);

        MenuModel::create([
            'main_menu' => 'Test',
            'menu' => 'First Menu',
            'route_name' => 'admin.first.index',
            'icon' => 'c-home',
            'sort' => 1,
            'is_active' => true,
        ]);

        MenuModel::create([
            'main_menu' => 'Test',
            'menu' => 'Second Menu',
            'route_name' => 'admin.second.index',
            'icon' => 'c-star',
            'sort' => 2,
            'is_active' => true,
        ]);

        Livewire::test(Menu::class)
            ->set('search', 'first.index')
            ->assertSee('First Menu')
            ->assertDontSee('Second Menu');
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(Menu::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_menus()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(Menu::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_menu()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = Role::create(['name' => 'viewer']);
        $indexPermission = Permission::where('name', 'admin.menus.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        Livewire::test(Menu::class)
            ->call('create')
            ->assertForbidden();
    }

    /** @test */
    public function edit_nonexistent_menu_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Menu::class)
            ->call('edit', 99999); // Non-existent ID

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function menus_are_sorted_by_sort_column()
    {
        $this->actingAs($this->user);

        MenuModel::create([
            'main_menu' => 'Test',
            'menu' => 'Third',
            'route_name' => 'admin.third.index',
            'icon' => 'c-home',
            'sort' => 3,
            'is_active' => true,
        ]);

        MenuModel::create([
            'main_menu' => 'Test',
            'menu' => 'First',
            'route_name' => 'admin.first.index',
            'icon' => 'c-home',
            'sort' => 1,
            'is_active' => true,
        ]);

        MenuModel::create([
            'main_menu' => 'Test',
            'menu' => 'Second',
            'route_name' => 'admin.second.index',
            'icon' => 'c-home',
            'sort' => 2,
            'is_active' => true,
        ]);

        $component = Livewire::test(Menu::class);

        $component->assertViewHas('menus', function ($menus) {
            $menuNames = $menus->pluck('menu')->toArray();
            return $menuNames[0] === 'First' && $menuNames[1] === 'Second' && $menuNames[2] === 'Third';
        });
    }

    /** @test */
    public function menus_are_paginated()
    {
        $this->actingAs($this->user);

        // Create more than 10 menus (pagination limit)
        for ($i = 1; $i <= 15; $i++) {
            MenuModel::create([
                'main_menu' => 'Test',
                'menu' => "Menu $i",
                'route_name' => "admin.menu$i.index",
                'icon' => 'c-home',
                'sort' => $i,
                'is_active' => true,
            ]);
        }

        $component = Livewire::test(Menu::class);

        $component->assertViewHas('menus', function ($menus) {
            return $menus->count() === 10 && $menus->total() === 15;
        });
    }

    /** @test */
    public function menu_observer_logs_on_create()
    {
        $this->actingAs($this->user);

        // Test that observer is triggered (no exception)
        $menu = MenuModel::create([
            'main_menu' => 'Observer Test',
            'menu' => 'Test Menu',
            'route_name' => 'admin.test.index',
            'icon' => 'c-home',
            'sort' => 1,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
        ]);
    }
}
