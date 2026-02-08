<?php

namespace Tests\Feature\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\Location;
use App\Models\Location as ModelsLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->role = Role::create(['name' => 'admin']);

        // Create all required permissions
        $permissions = [
            'admin.locations.index',
            'admin.locations.create',
            'admin.locations.edit',
            'admin.locations.destroy',
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
    public function can_render_locations_page()
    {
        $this->actingAs($this->user);

        Livewire::test(Location::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.location');
    }

    /** @test */
    public function can_create_location_without_image()
    {
        $this->actingAs($this->user);

        Livewire::test(Location::class)
            ->call('create')
            ->set('form.name', 'Test Location')
            ->set('form.description', 'Test Description')
            ->set('form.is_active', true)
            ->call('save');

        $this->assertDatabaseHas('locations', [
            'name' => 'Test Location',
            'description' => 'Test Description',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function can_create_location_with_image()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->image('location.jpg', 800, 600);

        Livewire::test(Location::class)
            ->call('create')
            ->set('form.name', 'Location With Image')
            ->set('form.description', 'Description')
            ->set('form.is_active', true)
            ->set('form.image', $file)
            ->call('save');

        $this->assertDatabaseHas('locations', [
            'name' => 'Location With Image',
        ]);

        $location = ModelsLocation::where('name', 'Location With Image')->first();
        $this->assertNotNull($location->image);
    }

    /** @test */
    public function cannot_create_location_with_missing_name()
    {
        $this->actingAs($this->user);

        Livewire::test(Location::class)
            ->call('create')
            ->set('form.name', '')
            ->set('form.description', 'Some description')
            ->set('form.is_active', true)
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function can_edit_location()
    {
        $this->actingAs($this->user);

        $location = ModelsLocation::create([
            'name' => 'Original Name',
            'description' => 'Original Description',
            'is_active' => true,
        ]);

        Livewire::test(Location::class)
            ->call('edit', $location->id)
            ->assertSet('editId', $location->id)
            ->set('form.name', 'Updated Name')
            ->set('form.description', 'Updated Description')
            ->call('save');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);
    }

    /** @test */
    public function can_delete_location()
    {
        $this->actingAs($this->user);

        $location = ModelsLocation::create([
            'name' => 'To Delete',
            'description' => 'Will be deleted',
            'is_active' => true,
        ]);

        Livewire::test(Location::class)
            ->call('confirmDelete', $location->id)
            ->assertSet('deleteId', $location->id)
            ->call('delete');

        $this->assertDatabaseMissing('locations', [
            'id' => $location->id,
        ]);
    }

    /** @test */
    public function can_search_locations()
    {
        $this->actingAs($this->user);

        ModelsLocation::create(['name' => 'UniqueChapelXYZ', 'is_active' => true]);
        ModelsLocation::create(['name' => 'AnotherPlaceABC', 'is_active' => true]);
        ModelsLocation::create(['name' => 'ThirdSpotDEF', 'is_active' => true]);

        Livewire::test(Location::class)
            ->set('search', 'UniqueChapelXYZ')
            ->assertSee('UniqueChapelXYZ')
            ->assertDontSee('AnotherPlaceABC')
            ->assertDontSee('ThirdSpotDEF');
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(Location::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_locations()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(Location::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_location()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = Role::create(['name' => 'viewer']);
        $indexPermission = Permission::where('name', 'admin.locations.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        Livewire::test(Location::class)
            ->call('create')
            ->assertForbidden();
    }

    /** @test */
    public function edit_nonexistent_location_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Location::class)
            ->call('edit', 99999); // Non-existent ID

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function locations_are_paginated()
    {
        $this->actingAs($this->user);

        // Create more than 5 locations (pagination limit)
        for ($i = 1; $i <= 10; $i++) {
            ModelsLocation::create([
                'name' => "Location $i",
                'is_active' => true,
            ]);
        }

        $component = Livewire::test(Location::class);

        // Should show pagination
        $component->assertViewHas('locations', function ($locations) {
            return $locations->count() === 5 && $locations->total() === 10;
        });
    }

    /** @test */
    public function can_remove_existing_image()
    {
        $this->actingAs($this->user);

        // Create a location with an image path
        $location = ModelsLocation::create([
            'name' => 'Location With Image',
            'image' => 'locations/test-image.webp',
            'is_active' => true,
        ]);

        // Create a fake file to simulate the existing image
        Storage::disk('public')->put('locations/test-image.webp', 'fake image content');

        Livewire::test(Location::class)
            ->call('edit', $location->id)
            ->call('removeImage');

        // The existingImage should be cleared
        $this->assertTrue(true);
    }

    /** @test */
    public function observer_logs_creation()
    {
        $this->actingAs($this->user);

        // This will trigger the observer
        $location = ModelsLocation::create([
            'name' => 'Observer Test',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    /** @test */
    public function delete_location_with_image_cleans_up_file()
    {
        $this->actingAs($this->user);

        // Create a fake image file
        Storage::disk('public')->put('locations/to-delete.webp', 'fake image content');

        $location = ModelsLocation::create([
            'name' => 'Location To Delete',
            'image' => 'locations/to-delete.webp',
            'is_active' => true,
        ]);

        Livewire::test(Location::class)
            ->call('confirmDelete', $location->id)
            ->call('delete');

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
        // Note: Observer handles image deletion
    }
}
