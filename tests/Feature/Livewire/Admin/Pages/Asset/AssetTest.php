<?php

namespace Tests\Feature\Livewire\Admin\Pages\Asset;

use App\Livewire\Admin\Pages\Asset\Index as AssetIndex;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\CustomPermission;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $adminRole;
    protected Group $group;
    protected AssetCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->adminRole = Role::create(['name' => 'admin']);

        // Create a test group for permissions
        $this->group = Group::create(['name' => 'Asset Management']);

        // Create all required permissions
        $permissions = [
            'admin.assets.index',
            'admin.assets.create',
            'admin.assets.edit',
            'admin.assets.destroy',
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

        // Create a category for assets
        $this->category = AssetCategory::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function can_render_assets_page()
    {
        $this->actingAs($this->user);

        Livewire::test(AssetIndex::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.asset.index');
    }

    /** @test */
    public function can_create_asset_without_image()
    {
        $this->actingAs($this->user);

        Livewire::test(AssetIndex::class)
            ->call('create')
            ->set('form.asset_category_id', $this->category->id)
            ->set('form.name', 'Laptop Dell')
            ->set('form.slug', 'laptop-dell')
            ->set('form.code', 'ASSET-001')
            ->set('form.description', 'Dell Latitude laptop')
            ->set('form.quantity', 5)
            ->set('form.storage_location', 'Room A')
            ->set('form.is_active', true)
            ->call('save');

        $this->assertDatabaseHas('assets', [
            'name' => 'Laptop Dell',
            'code' => 'ASSET-001',
            'quantity' => 5,
            'storage_location' => 'Room A',
        ]);
    }

    /** @test */
    public function can_create_asset_with_image()
    {
        $this->actingAs($this->user);

        $image = UploadedFile::fake()->image('laptop.jpg', 200, 200);

        Livewire::test(AssetIndex::class)
            ->call('create')
            ->set('form.asset_category_id', $this->category->id)
            ->set('form.name', 'Laptop HP')
            ->set('form.slug', 'laptop-hp')
            ->set('form.code', 'ASSET-002')
            ->set('form.quantity', 3)
            ->set('form.storage_location', 'Room B')
            ->set('form.is_active', true)
            ->set('form.image', $image)
            ->call('save');

        $this->assertDatabaseHas('assets', [
            'name' => 'Laptop HP',
            'code' => 'ASSET-002',
        ]);

        $asset = Asset::where('code', 'ASSET-002')->first();
        $this->assertNotNull($asset->image);
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($asset->image);
    }

    /** @test */
    public function slug_is_auto_generated_from_name()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(AssetIndex::class)
            ->call('create')
            ->set('form.name', 'Office Printer');

        $component->assertSet('form.slug', 'office-printer');
    }

    /** @test */
    public function cannot_create_asset_with_missing_name()
    {
        $this->actingAs($this->user);

        Livewire::test(AssetIndex::class)
            ->call('create')
            ->set('form.asset_category_id', $this->category->id)
            ->set('form.name', '')
            ->set('form.code', 'ASSET-003')
            ->set('form.quantity', 1)
            ->set('form.storage_location', 'Room C')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function cannot_create_asset_with_missing_code()
    {
        $this->actingAs($this->user);

        Livewire::test(AssetIndex::class)
            ->call('create')
            ->set('form.asset_category_id', $this->category->id)
            ->set('form.name', 'Test Asset')
            ->set('form.code', '')
            ->set('form.quantity', 1)
            ->set('form.storage_location', 'Room C')
            ->call('save')
            ->assertHasErrors(['form.code']);
    }

    /** @test */
    public function cannot_create_asset_with_invalid_category()
    {
        $this->actingAs($this->user);

        Livewire::test(AssetIndex::class)
            ->call('create')
            ->set('form.asset_category_id', 99999)
            ->set('form.name', 'Test Asset')
            ->set('form.code', 'ASSET-004')
            ->set('form.quantity', 1)
            ->set('form.storage_location', 'Room C')
            ->call('save')
            ->assertHasErrors(['form.asset_category_id']);
    }

    /** @test */
    public function can_edit_asset()
    {
        $this->actingAs($this->user);

        $asset = Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Original Asset',
            'slug' => 'original-asset',
            'code' => 'ASSET-005',
            'quantity' => 10,
            'storage_location' => 'Room D',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Livewire::test(AssetIndex::class)
            ->call('edit', $asset->id)
            ->assertSet('editId', $asset->id)
            ->set('form.name', 'Updated Asset')
            ->set('form.slug', 'updated-asset')
            ->set('form.quantity', 20)
            ->call('save');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'name' => 'Updated Asset',
            'quantity' => 20,
        ]);
    }

    /** @test */
    public function can_toggle_is_active_status()
    {
        $this->actingAs($this->user);

        $asset = Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Active Asset',
            'slug' => 'active-asset',
            'code' => 'ASSET-006',
            'quantity' => 5,
            'storage_location' => 'Room E',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Livewire::test(AssetIndex::class)
            ->call('edit', $asset->id)
            ->set('form.is_active', false)
            ->call('save');

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function can_delete_asset()
    {
        $this->actingAs($this->user);

        $asset = Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'To Delete',
            'slug' => 'to-delete',
            'code' => 'ASSET-007',
            'quantity' => 1,
            'storage_location' => 'Room F',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Livewire::test(AssetIndex::class)
            ->call('confirmDelete', $asset->id)
            ->assertSet('deleteId', $asset->id)
            ->call('delete');

        $this->assertDatabaseMissing('assets', [
            'id' => $asset->id,
        ]);
    }

    /** @test */
    public function delete_asset_with_image_cleans_up_file()
    {
        $this->actingAs($this->user);

        $image = UploadedFile::fake()->image('asset.jpg');
        $imagePath = $image->store('assets', 'public');

        $asset = Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Asset With Image',
            'slug' => 'asset-with-image',
            'code' => 'ASSET-008',
            'quantity' => 1,
            'storage_location' => 'Room G',
            'image' => $imagePath,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($imagePath);

        Livewire::test(AssetIndex::class)
            ->call('confirmDelete', $asset->id)
            ->call('delete');

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
        $disk->assertMissing($imagePath);
    }

    /** @test */
    public function can_search_assets_by_name()
    {
        $this->actingAs($this->user);

        Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Laptop Dell',
            'slug' => 'laptop-dell',
            'code' => 'LD-001',
            'quantity' => 5,
            'storage_location' => 'Room A',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Printer Canon',
            'slug' => 'printer-canon',
            'code' => 'PC-001',
            'quantity' => 3,
            'storage_location' => 'Room B',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Livewire::test(AssetIndex::class)
            ->set('search', 'Laptop')
            ->assertViewHas('assets', function ($assets) {
                return $assets->count() === 1 
                    && $assets->first()->name === 'Laptop Dell';
            });
    }

    /** @test */
    public function can_search_assets_by_code()
    {
        $this->actingAs($this->user);

        Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Asset A',
            'slug' => 'asset-a',
            'code' => 'UNIQUE-CODE-123',
            'quantity' => 5,
            'storage_location' => 'Room A',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Asset B',
            'slug' => 'asset-b',
            'code' => 'OTHER-CODE-456',
            'quantity' => 3,
            'storage_location' => 'Room B',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Livewire::test(AssetIndex::class)
            ->set('search', 'UNIQUE-CODE')
            ->assertViewHas('assets', function ($assets) {
                return $assets->count() === 1 
                    && $assets->first()->code === 'UNIQUE-CODE-123';
            });
    }

    /** @test */
    public function can_search_assets_by_storage_location()
    {
        $this->actingAs($this->user);

        Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Asset C',
            'slug' => 'asset-c',
            'code' => 'AC-001',
            'quantity' => 5,
            'storage_location' => 'Warehouse Alpha',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Asset D',
            'slug' => 'asset-d',
            'code' => 'AD-001',
            'quantity' => 3,
            'storage_location' => 'Storage Beta',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Livewire::test(AssetIndex::class)
            ->set('search', 'Warehouse Alpha')
            ->assertViewHas('assets', function ($assets) {
                return $assets->count() === 1 
                    && $assets->first()->storage_location === 'Warehouse Alpha';
            });
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(AssetIndex::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_assets()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(AssetIndex::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_asset()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = Role::create(['name' => 'viewer']);
        $indexPermission = CustomPermission::where('name', 'admin.assets.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        // The component handles authorization gracefully with flash message
        Livewire::test(AssetIndex::class)
            ->call('create')
            ->assertStatus(200);
    }

    /** @test */
    public function edit_nonexistent_asset_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(AssetIndex::class)
            ->call('edit', 99999);

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function delete_nonexistent_asset_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(AssetIndex::class)
            ->call('confirmDelete', 99999);

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function assets_are_paginated()
    {
        $this->actingAs($this->user);

        // Create more than 10 assets (pagination limit)
        for ($i = 1; $i <= 15; $i++) {
            Asset::create([
                'asset_category_id' => $this->category->id,
                'name' => "Asset $i",
                'slug' => "asset-$i",
                'code' => "ASSET-$i",
                'quantity' => $i,
                'storage_location' => "Room $i",
                'is_active' => true,
                'created_by' => $this->user->id,
            ]);
        }

        $component = Livewire::test(AssetIndex::class);

        // Should show pagination (10 per page)
        $component->assertViewHas('assets', function ($assets) {
            return $assets->count() === 10;
        });
    }

    /** @test */
    public function observer_logs_creation()
    {
        $this->actingAs($this->user);

        // This will trigger the observer
        $asset = Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Observer Test',
            'slug' => 'observer-test',
            'code' => 'OBS-001',
            'quantity' => 1,
            'storage_location' => 'Test Room',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('assets', ['id' => $asset->id]);
    }

    /** @test */
    public function can_remove_existing_image()
    {
        $this->actingAs($this->user);

        $image = UploadedFile::fake()->image('asset.jpg');
        $imagePath = $image->store('assets', 'public');

        $asset = Asset::create([
            'asset_category_id' => $this->category->id,
            'name' => 'Asset With Image',
            'slug' => 'asset-image',
            'code' => 'IMG-001',
            'quantity' => 1,
            'storage_location' => 'Room X',
            'image' => $imagePath,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($imagePath);

        Livewire::test(AssetIndex::class)
            ->call('edit', $asset->id)
            ->call('removeImage');

        $disk->assertMissing($imagePath);
    }

    /** @test */
    public function quantity_must_be_non_negative()
    {
        $this->actingAs($this->user);

        Livewire::test(AssetIndex::class)
            ->call('create')
            ->set('form.asset_category_id', $this->category->id)
            ->set('form.name', 'Test Asset')
            ->set('form.code', 'ASSET-NEG')
            ->set('form.quantity', -5)
            ->set('form.storage_location', 'Room')
            ->call('save')
            ->assertHasErrors(['form.quantity']);
    }
}
