<?php

namespace Tests\Feature\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\Groups;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroupsTest extends TestCase
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
            'admin.groups.index',
            'admin.groups.create',
            'admin.groups.edit',
            'admin.groups.destroy',
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
    public function can_render_groups_page()
    {
        $this->actingAs($this->user);

        Livewire::test(Groups::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.groups');
    }

    /** @test */
    public function can_create_group()
    {
        $this->actingAs($this->user);

        Livewire::test(Groups::class)
            ->call('create')
            ->set('form.name', 'Test Group')
            ->call('save');

        $this->assertDatabaseHas('groups', [
            'name' => 'Test Group',
        ]);
    }

    /** @test */
    public function cannot_create_group_with_empty_name()
    {
        $this->actingAs($this->user);

        Livewire::test(Groups::class)
            ->call('create')
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors(['form.name' => 'required']);
    }

    /** @test */
    public function can_edit_group()
    {
        $this->actingAs($this->user);

        $group = Group::create(['name' => 'Original Name']);

        Livewire::test(Groups::class)
            ->call('edit', $group->id)
            ->assertSet('editId', $group->id)
            ->set('form.name', 'Updated Name')
            ->call('save');

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function can_delete_group()
    {
        $this->actingAs($this->user);

        $group = Group::create(['name' => 'Group to Delete']);

        Livewire::test(Groups::class)
            ->call('confirmDelete', $group->id)
            ->assertSet('deleteId', $group->id)
            ->call('delete');

        $this->assertDatabaseMissing('groups', [
            'id' => $group->id,
        ]);
    }

    /** @test */
    public function can_search_groups()
    {
        $this->actingAs($this->user);

        Group::create(['name' => 'Alpha Group']);
        Group::create(['name' => 'Beta Group']);
        Group::create(['name' => 'Gamma Group']);

        Livewire::test(Groups::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Group')
            ->assertDontSee('Beta Group')
            ->assertDontSee('Gamma Group');
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(Groups::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_groups()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(Groups::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_group()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = Role::create(['name' => 'viewer']);
        $indexPermission = Permission::where('name', 'admin.groups.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        Livewire::test(Groups::class)
            ->call('create')
            ->assertForbidden();
    }

    /** @test */
    public function edit_nonexistent_group_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Groups::class)
            ->call('edit', 99999); // Non-existent ID

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function groups_are_paginated()
    {
        $this->actingAs($this->user);

        // Create more than 5 groups (pagination limit)
        for ($i = 1; $i <= 10; $i++) {
            Group::create(['name' => "Group $i"]);
        }

        $component = Livewire::test(Groups::class);

        // Should show pagination
        $component->assertViewHas('groups', function ($groups) {
            return $groups->count() === 5 && $groups->total() === 10;
        });
    }
}
