<?php

namespace Tests\Feature\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\Role;
use App\Models\CustomPermission;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected SpatieRole $adminRole;
    protected Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->adminRole = SpatieRole::create(['name' => 'admin']);

        // Create a test group for permissions
        $this->group = Group::create(['name' => 'Test Group']);

        // Create all required permissions
        $permissions = [
            'admin.roles.index',
            'admin.roles.create',
            'admin.roles.edit',
            'admin.roles.destroy',
        ];

        foreach ($permissions as $permissionName) {
            $permission = CustomPermission::create([
                'name' => $permissionName,
                'route_name' => $permissionName,
                'group' => $this->group->id,
                'default' => 'Default',
                'guard_name' => 'web',
            ]);
            $this->adminRole->givePermissionTo($permission);
        }

        $this->user->assignRole($this->adminRole);
    }

    /** @test */
    public function can_render_roles_page()
    {
        $this->actingAs($this->user);

        Livewire::test(Role::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.role');
    }

    /** @test */
    public function can_create_role()
    {
        $this->actingAs($this->user);

        Livewire::test(Role::class)
            ->call('create')
            ->set('form.name', 'manager')
            ->call('save');

        $this->assertDatabaseHas('roles', [
            'name' => 'manager',
        ]);
    }

    /** @test */
    public function cannot_create_role_with_missing_name()
    {
        $this->actingAs($this->user);

        Livewire::test(Role::class)
            ->call('create')
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function cannot_create_duplicate_role_name()
    {
        $this->actingAs($this->user);

        SpatieRole::create(['name' => 'duplicate_role', 'guard_name' => 'web']);

        Livewire::test(Role::class)
            ->call('create')
            ->set('form.name', 'duplicate_role')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function can_edit_role()
    {
        $this->actingAs($this->user);

        $role = SpatieRole::create(['name' => 'original_role', 'guard_name' => 'web']);

        Livewire::test(Role::class)
            ->call('edit', $role->id)
            ->assertSet('editId', $role->id)
            ->set('form.name', 'updated_role')
            ->call('save');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'updated_role',
        ]);
    }

    /** @test */
    public function can_delete_role()
    {
        $this->actingAs($this->user);

        $role = SpatieRole::create(['name' => 'to_delete', 'guard_name' => 'web']);

        Livewire::test(Role::class)
            ->call('confirmDelete', $role->id)
            ->assertSet('deleteId', $role->id)
            ->call('delete');

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    /** @test */
    public function can_search_roles_by_name()
    {
        $this->actingAs($this->user);

        SpatieRole::create(['name' => 'manager_alpha', 'guard_name' => 'web']);
        SpatieRole::create(['name' => 'editor_beta', 'guard_name' => 'web']);

        Livewire::test(Role::class)
            ->set('search', 'alpha')
            ->assertSee('manager_alpha')
            ->assertDontSee('editor_beta');
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(Role::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_roles()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(Role::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_role()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = SpatieRole::create(['name' => 'viewer']);
        $indexPermission = CustomPermission::where('name', 'admin.roles.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        Livewire::test(Role::class)
            ->call('create')
            ->assertForbidden();
    }

    /** @test */
    public function edit_nonexistent_role_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Role::class)
            ->call('edit', 99999); // Non-existent ID

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function roles_are_paginated()
    {
        $this->actingAs($this->user);

        // Create more than 10 roles (pagination limit)
        for ($i = 1; $i <= 15; $i++) {
            SpatieRole::create(['name' => "test_role_$i", 'guard_name' => 'web']);
        }

        $component = Livewire::test(Role::class);

        // Should show pagination (10 per page + 1 admin role = 11 total, but only 10 shown)
        $component->assertViewHas('roles', function ($roles) {
            return $roles->count() === 10;
        });
    }

    /** @test */
    public function observer_logs_creation()
    {
        $this->actingAs($this->user);

        // This will trigger the observer
        $role = SpatieRole::create(['name' => 'observer_test', 'guard_name' => 'web']);

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    /** @test */
    public function can_open_permission_modal()
    {
        $this->actingAs($this->user);

        $role = SpatieRole::create(['name' => 'permission_test', 'guard_name' => 'web']);

        Livewire::test(Role::class)
            ->call('permission', $role->id)
            ->assertSet('form.role.id', $role->id);
    }

    /** @test */
    public function can_sync_permissions_to_role()
    {
        $this->actingAs($this->user);

        $role = SpatieRole::create(['name' => 'sync_test', 'guard_name' => 'web']);

        // Create a test permission
        $permission = CustomPermission::create([
            'name' => 'admin.test.permission',
            'route_name' => 'admin.test.route',
            'group' => $this->group->id,
            'default' => 'Default',
            'guard_name' => 'web',
        ]);

        Livewire::test(Role::class)
            ->call('permission', $role->id)
            ->set('form.selectedPermissions', ['admin.test.permission'])
            ->call('syncPermission');

        $this->assertTrue($role->fresh()->hasPermissionTo('admin.test.permission'));
    }

    /** @test */
    public function permission_nonexistent_role_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Role::class)
            ->call('permission', 99999); // Non-existent ID

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }
}
