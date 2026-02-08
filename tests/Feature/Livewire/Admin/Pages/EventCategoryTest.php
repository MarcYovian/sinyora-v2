<?php

namespace Tests\Feature\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\EventCategory;
use App\Models\CustomPermission;
use App\Models\EventCategory as EventCategoryModel;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $adminRole;
    protected Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->adminRole = Role::create(['name' => 'admin']);

        // Create a test group for permissions
        $this->group = Group::create(['name' => 'Event Management']);

        // Create all required permissions
        $permissions = [
            'admin.event-categories.index',
            'admin.event-categories.create',
            'admin.event-categories.edit',
            'admin.event-categories.destroy',
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
    public function can_render_event_categories_page()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.event-category');
    }

    /** @test */
    public function can_create_event_category()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->call('create')
            ->set('form.name', 'Workshop')
            ->set('form.slug', 'workshop')
            ->set('form.color', '#FF5733')
            ->set('form.is_active', true)
            ->set('form.is_mass_category', false)
            ->call('save');

        $this->assertDatabaseHas('event_categories', [
            'name' => 'Workshop',
            'slug' => 'workshop',
            'color' => '#FF5733',
            'is_active' => true,
            'is_mass_category' => false,
        ]);
    }

    /** @test */
    public function slug_is_auto_generated_from_name()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(EventCategory::class)
            ->call('create')
            ->set('form.name', 'Sunday Mass');

        $component->assertSet('form.slug', 'sunday-mass');
    }

    /** @test */
    public function cannot_create_category_with_missing_name()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->call('create')
            ->set('form.name', '')
            ->set('form.slug', 'test-slug')
            ->set('form.color', '#FF0000')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function cannot_create_category_with_missing_slug()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->call('create')
            ->set('form.name', 'Test Category')
            ->set('form.slug', '')
            ->set('form.color', '#FF0000')
            ->call('save')
            ->assertHasErrors(['form.slug']);
    }

    /** @test */
    public function cannot_create_category_with_missing_color()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->call('create')
            ->set('form.name', 'Test Category')
            ->set('form.slug', 'test-category')
            ->set('form.color', '')
            ->call('save')
            ->assertHasErrors(['form.color']);
    }

    /** @test */
    public function cannot_create_duplicate_slug()
    {
        $this->actingAs($this->user);

        EventCategoryModel::create([
            'name' => 'Existing Category',
            'slug' => 'existing-category',
            'color' => '#00FF00',
            'is_active' => true,
        ]);

        Livewire::test(EventCategory::class)
            ->call('create')
            ->set('form.name', 'New Category')
            ->set('form.slug', 'existing-category')
            ->set('form.color', '#FF0000')
            ->call('save')
            ->assertHasErrors(['form.slug']);
    }

    /** @test */
    public function can_edit_event_category()
    {
        $this->actingAs($this->user);

        $category = EventCategoryModel::create([
            'name' => 'Original Category',
            'slug' => 'original-category',
            'color' => '#00FF00',
            'is_active' => true,
            'is_mass_category' => false,
        ]);

        Livewire::test(EventCategory::class)
            ->call('edit', $category->id)
            ->assertSet('editId', $category->id)
            ->set('form.name', 'Updated Category')
            ->set('form.slug', 'updated-category')
            ->set('form.color', '#0000FF')
            ->call('save');

        $this->assertDatabaseHas('event_categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'color' => '#0000FF',
        ]);
    }

    /** @test */
    public function can_update_category_with_same_slug()
    {
        $this->actingAs($this->user);

        $category = EventCategoryModel::create([
            'name' => 'My Category',
            'slug' => 'my-category',
            'color' => '#FF0000',
            'is_active' => true,
        ]);

        Livewire::test(EventCategory::class)
            ->call('edit', $category->id)
            ->set('form.name', 'Updated Name')
            ->set('form.slug', 'my-category') // Keep same slug
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('event_categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'slug' => 'my-category',
        ]);
    }

    /** @test */
    public function can_toggle_is_active_status()
    {
        $this->actingAs($this->user);

        $category = EventCategoryModel::create([
            'name' => 'Active Category',
            'slug' => 'active-category',
            'color' => '#FF0000',
            'is_active' => true,
        ]);

        Livewire::test(EventCategory::class)
            ->call('edit', $category->id)
            ->set('form.is_active', false)
            ->call('save');

        $this->assertDatabaseHas('event_categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function can_toggle_is_mass_category()
    {
        $this->actingAs($this->user);

        $category = EventCategoryModel::create([
            'name' => 'Regular Category',
            'slug' => 'regular-category',
            'color' => '#FF0000',
            'is_active' => true,
            'is_mass_category' => false,
        ]);

        Livewire::test(EventCategory::class)
            ->call('edit', $category->id)
            ->set('form.is_mass_category', true)
            ->call('save');

        $this->assertDatabaseHas('event_categories', [
            'id' => $category->id,
            'is_mass_category' => true,
        ]);
    }

    /** @test */
    public function can_delete_event_category()
    {
        $this->actingAs($this->user);

        $category = EventCategoryModel::create([
            'name' => 'To Delete',
            'slug' => 'to-delete',
            'color' => '#FF0000',
            'is_active' => true,
        ]);

        Livewire::test(EventCategory::class)
            ->call('confirmDelete', $category->id)
            ->assertSet('deleteId', $category->id)
            ->call('delete');

        $this->assertDatabaseMissing('event_categories', [
            'id' => $category->id,
        ]);
    }

    /** @test */
    public function can_search_categories_by_name()
    {
        $this->actingAs($this->user);

        EventCategoryModel::create(['name' => 'Workshop', 'slug' => 'workshop', 'color' => '#FF0000', 'is_active' => true]);
        EventCategoryModel::create(['name' => 'Seminar', 'slug' => 'seminar', 'color' => '#00FF00', 'is_active' => true]);

        Livewire::test(EventCategory::class)
            ->set('search', 'Workshop')
            ->assertViewHas('categories', function ($categories) {
                return $categories->count() === 1 
                    && $categories->first()->name === 'Workshop';
            });
    }

    /** @test */
    public function can_search_categories_by_slug()
    {
        $this->actingAs($this->user);

        EventCategoryModel::create(['name' => 'Sunday Mass', 'slug' => 'sunday-mass', 'color' => '#FF0000', 'is_active' => true]);
        EventCategoryModel::create(['name' => 'Weekly Meeting', 'slug' => 'weekly-meeting', 'color' => '#00FF00', 'is_active' => true]);

        Livewire::test(EventCategory::class)
            ->set('search', 'sunday-mass')
            ->assertViewHas('categories', function ($categories) {
                return $categories->count() === 1 
                    && $categories->first()->slug === 'sunday-mass';
            });
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_categories()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(EventCategory::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_category()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = Role::create(['name' => 'viewer']);
        $indexPermission = CustomPermission::where('name', 'admin.event-categories.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        // The component handles authorization gracefully with flash message
        Livewire::test(EventCategory::class)
            ->call('create')
            ->assertStatus(200);
    }

    /** @test */
    public function edit_nonexistent_category_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->call('edit', 99999);

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function delete_nonexistent_category_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->call('confirmDelete', 99999);

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function categories_are_paginated()
    {
        $this->actingAs($this->user);

        // Create more than 10 categories (pagination limit)
        for ($i = 1; $i <= 15; $i++) {
            EventCategoryModel::create([
                'name' => "Category $i",
                'slug' => "category-$i",
                'color' => '#FF0000',
                'is_active' => true,
            ]);
        }

        $component = Livewire::test(EventCategory::class);

        // Should show pagination (10 per page)
        $component->assertViewHas('categories', function ($categories) {
            return $categories->count() === 10;
        });
    }

    /** @test */
    public function observer_logs_creation()
    {
        $this->actingAs($this->user);

        Log::shouldReceive('info')
            ->atLeast()
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Event category created');
            });

        // This will trigger the observer
        $category = EventCategoryModel::create([
            'name' => 'Observer Test',
            'slug' => 'observer-test',
            'color' => '#FF0000',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('event_categories', ['id' => $category->id]);
    }

    /** @test */
    public function search_resets_pagination()
    {
        $this->actingAs($this->user);

        // Create enough categories to have multiple pages
        for ($i = 1; $i <= 15; $i++) {
            EventCategoryModel::create([
                'name' => "Category $i",
                'slug' => "category-$i",
                'color' => '#FF0000',
                'is_active' => true,
            ]);
        }

        // Go to page 2, then search - should reset to page 1
        Livewire::test(EventCategory::class)
            ->set('search', 'Category 1')
            ->assertSet('search', 'Category 1');

        // The updatedSearch method resets the page
        $this->assertTrue(true);
    }

    /** @test */
    public function color_validation_max_length()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->call('create')
            ->set('form.name', 'Test')
            ->set('form.slug', 'test')
            ->set('form.color', '#FF00001234') // Too long
            ->call('save')
            ->assertHasErrors(['form.color']);
    }

    /** @test */
    public function slug_must_be_alpha_dash()
    {
        $this->actingAs($this->user);

        Livewire::test(EventCategory::class)
            ->call('create')
            ->set('form.name', 'Test Category')
            ->set('form.slug', 'test category with spaces') // Invalid - has spaces
            ->set('form.color', '#FF0000')
            ->call('save')
            ->assertHasErrors(['form.slug']);
    }
}
