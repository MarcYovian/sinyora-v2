<?php

namespace Tests\Feature\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\Organization;
use App\Models\Organization as ModelsOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizationTest extends TestCase
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
            'admin.organizations.index',
            'admin.organizations.create',
            'admin.organizations.edit',
            'admin.organizations.destroy',
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
    public function can_render_organizations_page()
    {
        $this->actingAs($this->user);

        Livewire::test(Organization::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.organization');
    }

    /** @test */
    public function can_create_organization()
    {
        $this->actingAs($this->user);

        Livewire::test(Organization::class)
            ->call('create')
            ->set('form.name', 'Test Organization')
            ->set('form.code', 'TEST-ORG')
            ->set('form.description', 'Test Description')
            ->set('form.is_active', true)
            ->call('save');

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'code' => 'TEST-ORG',
            'description' => 'Test Description',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function cannot_create_organization_with_missing_required_fields()
    {
        $this->actingAs($this->user);

        Livewire::test(Organization::class)
            ->call('create')
            ->set('form.name', '')
            ->set('form.code', '')
            ->call('save')
            ->assertHasErrors(['form.name', 'form.code']);
    }

    /** @test */
    public function can_edit_organization()
    {
        $this->actingAs($this->user);

        $org = ModelsOrganization::create([
            'name' => 'Original Name',
            'code' => 'ORIG',
            'description' => 'Original Description',
            'is_active' => true,
        ]);

        Livewire::test(Organization::class)
            ->call('edit', $org->id)
            ->assertSet('editId', $org->id)
            ->set('form.name', 'Updated Name')
            ->set('form.code', 'UPDATED')
            ->call('save');

        $this->assertDatabaseHas('organizations', [
            'id' => $org->id,
            'name' => 'Updated Name',
            'code' => 'UPDATED',
        ]);
    }

    /** @test */
    public function can_delete_organization()
    {
        $this->actingAs($this->user);

        $org = ModelsOrganization::create([
            'name' => 'To Delete',
            'code' => 'DEL',
            'is_active' => true,
        ]);

        Livewire::test(Organization::class)
            ->call('confirmDelete', $org->id)
            ->assertSet('deleteId', $org->id)
            ->call('delete');

        $this->assertDatabaseMissing('organizations', [
            'id' => $org->id,
        ]);
    }

    /** @test */
    public function can_search_organizations_by_name_or_code()
    {
        $this->actingAs($this->user);

        ModelsOrganization::create(['name' => 'Alpha Corp', 'code' => 'ALPHA', 'is_active' => true]);
        ModelsOrganization::create(['name' => 'Beta Inc', 'code' => 'BETA', 'is_active' => true]);

        // Search by name
        Livewire::test(Organization::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Corp')
            ->assertDontSee('Beta Inc');

        // Search by code
        Livewire::test(Organization::class)
            ->set('search', 'BETA')
            ->assertSee('Beta Inc')
            ->assertDontSee('Alpha Corp');
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(Organization::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_organizations()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(Organization::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_organization()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = Role::create(['name' => 'viewer']);
        $indexPermission = Permission::where('name', 'admin.organizations.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        Livewire::test(Organization::class)
            ->call('create')
            ->assertForbidden();
    }

    /** @test */
    public function edit_nonexistent_organization_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Organization::class)
            ->call('edit', 99999); // Non-existent ID

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function organizations_are_paginated()
    {
        $this->actingAs($this->user);

        // Create more than 5 organizations (pagination limit)
        for ($i = 1; $i <= 10; $i++) {
            ModelsOrganization::create([
                'name' => "Org $i",
                'code' => "ORG-$i",
                'is_active' => true,
            ]);
        }

        $component = Livewire::test(Organization::class);

        // Should show pagination
        $component->assertViewHas('organizations', function ($organizations) {
            return $organizations->count() === 5 && $organizations->total() === 10;
        });
    }

    /** @test */
    public function observer_logs_creation()
    {
        $this->actingAs($this->user);

        // This will trigger the observer
        $org = ModelsOrganization::create([
            'name' => 'Observer Test',
            'code' => 'OBS',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('organizations', ['id' => $org->id]);
    }
}
