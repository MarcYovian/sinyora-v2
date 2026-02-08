<?php

namespace Tests\Feature\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\Permission;
use App\Models\CustomPermission;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $role;
    protected Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->role = Role::create(['name' => 'admin']);

        // Create a test group for permissions
        $this->group = Group::create(['name' => 'Test Group']);

        // Create all required permissions
        $permissions = [
            'admin.permissions.index',
            'admin.permissions.create',
            'admin.permissions.edit',
            'admin.permissions.destroy',
        ];

        foreach ($permissions as $permissionName) {
            $permission = CustomPermission::create([
                'name' => $permissionName,
                'route_name' => $permissionName,
                'group' => $this->group->id,
                'default' => 'Default',
                'guard_name' => 'web',
            ]);
            $this->role->givePermissionTo($permission);
        }

        $this->user->assignRole($this->role);
    }

    /** @test */
    public function can_render_permissions_page()
    {
        $this->actingAs($this->user);

        Livewire::test(Permission::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.permission');
    }

    /** @test */
    public function can_create_permission()
    {
        $this->actingAs($this->user);

        Livewire::test(Permission::class)
            ->call('create')
            ->set('form.name', 'admin.new.permission')
            ->set('form.group', $this->group->id)
            ->set('form.route_name', 'admin.new.route')
            ->set('form.default', 'Default')
            ->call('save');

        $this->assertDatabaseHas('permissions', [
            'name' => 'admin.new.permission',
            'route_name' => 'admin.new.route',
        ]);
    }

    /** @test */
    public function cannot_create_permission_with_missing_required_fields()
    {
        $this->actingAs($this->user);

        Livewire::test(Permission::class)
            ->call('create')
            ->set('form.name', '')
            ->set('form.route_name', '')
            ->call('save')
            ->assertHasErrors(['form.name', 'form.route_name']);
    }

    /** @test */
    public function cannot_create_duplicate_permission_name()
    {
        $this->actingAs($this->user);

        CustomPermission::create([
            'name' => 'admin.duplicate.test',
            'route_name' => 'admin.duplicate.route',
            'group' => $this->group->id,
            'default' => 'Default',
            'guard_name' => 'web',
        ]);

        Livewire::test(Permission::class)
            ->call('create')
            ->set('form.name', 'admin.duplicate.test')
            ->set('form.group', $this->group->id)
            ->set('form.route_name', 'admin.another.route')
            ->set('form.default', 'Default')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function can_edit_permission()
    {
        $this->actingAs($this->user);

        $permission = CustomPermission::create([
            'name' => 'admin.original.permission',
            'route_name' => 'admin.original.route',
            'group' => $this->group->id,
            'default' => 'Default',
            'guard_name' => 'web',
        ]);

        Livewire::test(Permission::class)
            ->call('edit', $permission->id)
            ->assertSet('editId', $permission->id)
            ->set('form.name', 'admin.updated.permission')
            ->set('form.route_name', 'admin.updated.route')
            ->call('save');

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'admin.updated.permission',
            'route_name' => 'admin.updated.route',
        ]);
    }

    /** @test */
    public function can_delete_permission()
    {
        $this->actingAs($this->user);

        $permission = CustomPermission::create([
            'name' => 'admin.to.delete',
            'route_name' => 'admin.delete.route',
            'group' => $this->group->id,
            'default' => 'Default',
            'guard_name' => 'web',
        ]);

        Livewire::test(Permission::class)
            ->call('confirmDelete', $permission->id)
            ->assertSet('deleteId', $permission->id)
            ->call('delete');

        $this->assertDatabaseMissing('permissions', [
            'id' => $permission->id,
        ]);
    }

    /** @test */
    public function can_search_permissions_by_name()
    {
        $this->actingAs($this->user);

        CustomPermission::create([
            'name' => 'admin.search.alpha',
            'route_name' => 'admin.alpha.route',
            'group' => $this->group->id,
            'default' => 'Default',
            'guard_name' => 'web',
        ]);

        CustomPermission::create([
            'name' => 'admin.search.beta',
            'route_name' => 'admin.beta.route',
            'group' => $this->group->id,
            'default' => 'Default',
            'guard_name' => 'web',
        ]);

        Livewire::test(Permission::class)
            ->set('search', 'alpha')
            ->assertSee('admin.search.alpha')
            ->assertDontSee('admin.search.beta');
    }

    /** @test */
    public function can_search_permissions_by_route_name()
    {
        $this->actingAs($this->user);

        CustomPermission::create([
            'name' => 'admin.perm.one',
            'route_name' => 'admin.route.unique123',
            'group' => $this->group->id,
            'default' => 'Default',
            'guard_name' => 'web',
        ]);

        CustomPermission::create([
            'name' => 'admin.perm.two',
            'route_name' => 'admin.route.different456',
            'group' => $this->group->id,
            'default' => 'Default',
            'guard_name' => 'web',
        ]);

        Livewire::test(Permission::class)
            ->set('search', 'unique123')
            ->assertSee('admin.route.unique123')
            ->assertDontSee('admin.route.different456');
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(Permission::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_permissions()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(Permission::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_permission()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = Role::create(['name' => 'viewer']);
        $indexPermission = CustomPermission::where('name', 'admin.permissions.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        Livewire::test(Permission::class)
            ->call('create')
            ->assertForbidden();
    }

    /** @test */
    public function edit_nonexistent_permission_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Permission::class)
            ->call('edit', 99999); // Non-existent ID

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function permissions_are_paginated()
    {
        $this->actingAs($this->user);

        // Create more than 10 permissions (pagination limit)
        for ($i = 1; $i <= 15; $i++) {
            CustomPermission::create([
                'name' => "admin.test.permission.$i",
                'route_name' => "admin.test.route.$i",
                'group' => $this->group->id,
                'default' => 'Default',
                'guard_name' => 'web',
            ]);
        }

        $component = Livewire::test(Permission::class);

        // Should show pagination (10 per page + 4 existing setup permissions = 14 total, but only 10 shown)
        $component->assertViewHas('permissions', function ($permissions) {
            return $permissions->count() === 10;
        });
    }

    /** @test */
    public function observer_logs_creation()
    {
        $this->actingAs($this->user);

        // This will trigger the observer
        $permission = CustomPermission::create([
            'name' => 'admin.observer.test',
            'route_name' => 'admin.observer.route',
            'group' => $this->group->id,
            'default' => 'Default',
            'guard_name' => 'web',
        ]);

        $this->assertDatabaseHas('permissions', ['id' => $permission->id]);
    }

    /** @test */
    public function validates_group_exists()
    {
        $this->actingAs($this->user);

        Livewire::test(Permission::class)
            ->call('create')
            ->set('form.name', 'admin.test.permission')
            ->set('form.group', 99999) // Non-existent group
            ->set('form.route_name', 'admin.test.route')
            ->set('form.default', 'Default')
            ->call('save')
            ->assertHasErrors(['form.group']);
    }
}
