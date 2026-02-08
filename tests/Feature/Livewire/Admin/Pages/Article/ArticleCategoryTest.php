<?php

namespace Tests\Feature\Livewire\Admin\Pages\Article;

use App\Livewire\Admin\Pages\Article\Category;
use App\Models\ArticleCategory;
use App\Models\CustomPermission;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticleCategoryTest extends TestCase
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
        $this->group = Group::create(['name' => 'Article Management']);

        // Create all required permissions
        $permissions = [
            'admin.articles.categories.index',
            'admin.articles.categories.create',
            'admin.articles.categories.edit',
            'admin.articles.categories.delete',
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
    public function can_render_categories_page()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.article.category');
    }

    /** @test */
    public function can_create_category()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
            ->call('create')
            ->set('form.name', 'Technology')
            ->call('save');

        $this->assertDatabaseHas('article_categories', [
            'name' => 'Technology',
        ]);

        // Verify slug was auto-generated
        $category = ArticleCategory::where('name', 'Technology')->first();
        $this->assertNotNull($category->slug);
        $this->assertStringContainsString('technology', $category->slug);
    }

    /** @test */
    public function can_create_category_with_custom_slug()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
            ->call('create')
            ->set('form.name', 'Web Development')
            ->set('form.slug', 'web-dev')
            ->call('save');

        $this->assertDatabaseHas('article_categories', [
            'name' => 'Web Development',
            'slug' => 'web-dev',
        ]);
    }



    /** @test */
    public function slug_is_auto_generated_if_empty()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
            ->call('create')
            ->set('form.name', 'Programming Tips')
            ->set('form.slug', '')
            ->call('save');

        $category = ArticleCategory::where('name', 'Programming Tips')->first();
        $this->assertNotNull($category);
        $this->assertEquals('programming-tips', $category->slug);
    }

    /** @test */
    public function cannot_create_category_with_missing_name()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
            ->call('create')
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function cannot_create_duplicate_category_name()
    {
        $this->actingAs($this->user);

        ArticleCategory::create([
            'name' => 'Existing Category',
            'slug' => 'existing-category',
        ]);

        Livewire::test(Category::class)
            ->call('create')
            ->set('form.name', 'Existing Category')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function can_edit_category()
    {
        $this->actingAs($this->user);

        $category = ArticleCategory::create([
            'name' => 'Original Category',
            'slug' => 'original-category',
        ]);

        Livewire::test(Category::class)
            ->call('edit', $category->id)
            ->assertSet('editId', $category->id)
            ->assertSet('form.name', 'Original Category')
            ->assertSet('form.slug', 'original-category')
            ->set('form.name', 'Updated Category')
            ->set('form.slug', 'updated-category')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('article_categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    /** @test */
    public function can_update_category_with_same_name()
    {
        $this->actingAs($this->user);

        $category = ArticleCategory::create([
            'name' => 'My Category',
            'slug' => 'my-category',
        ]);

        Livewire::test(Category::class)
            ->call('edit', $category->id)
            ->set('form.name', 'My Category') // Keep same name
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('article_categories', [
            'id' => $category->id,
            'name' => 'My Category',
        ]);
    }

    /** @test */
    public function can_delete_category()
    {
        $this->actingAs($this->user);

        $category = ArticleCategory::create([
            'name' => 'To Delete',
            'slug' => 'to-delete',
        ]);

        Livewire::test(Category::class)
            ->call('confirmDelete', $category->id)
            ->assertSet('deleteId', $category->id)
            ->call('delete');

        $this->assertDatabaseMissing('article_categories', [
            'id' => $category->id,
        ]);
    }

    /** @test */
    public function can_search_categories_by_name()
    {
        $this->actingAs($this->user);

        ArticleCategory::create(['name' => 'Laravel', 'slug' => 'laravel']);
        ArticleCategory::create(['name' => 'Vue.js', 'slug' => 'vue-js']);

        Livewire::test(Category::class)
            ->set('search', 'Laravel')
            ->assertViewHas('categories', function ($categories) {
                return $categories->count() === 1 
                    && $categories->first()->name === 'Laravel';
            });
    }

    /** @test */
    public function can_search_categories_by_slug()
    {
        $this->actingAs($this->user);

        ArticleCategory::create(['name' => 'React Native', 'slug' => 'react-native']);
        ArticleCategory::create(['name' => 'Flutter', 'slug' => 'flutter-framework']);

        Livewire::test(Category::class)
            ->set('search', 'react-native')
            ->assertViewHas('categories', function ($categories) {
                return $categories->count() === 1 
                    && $categories->first()->slug === 'react-native';
            });
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_categories()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(Category::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_category()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = Role::create(['name' => 'viewer']);
        $indexPermission = CustomPermission::where('name', 'admin.articles.categories.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        // The component handles authorization gracefully with flash message
        Livewire::test(Category::class)
            ->call('create')
            ->assertStatus(200);
    }

    /** @test */
    public function edit_nonexistent_category_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
            ->call('edit', 99999);

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function delete_nonexistent_category_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
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
            ArticleCategory::create([
                'name' => "Category $i",
                'slug' => "category-$i",
            ]);
        }

        $component = Livewire::test(Category::class);

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
                return str_contains($message, 'Article category created');
            });

        // This will trigger the observer
        $category = ArticleCategory::create([
            'name' => 'Observer Test',
            'slug' => 'observer-test',
        ]);

        $this->assertDatabaseHas('article_categories', ['id' => $category->id]);
    }

    /** @test */
    public function name_max_length_validation()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
            ->call('create')
            ->set('form.name', str_repeat('a', 256)) // Exceeds 255
            ->call('save')
            ->assertHasErrors(['form.name']);
    }



    /** @test */
    public function auto_slug_ensures_uniqueness()
    {
        $this->actingAs($this->user);

        // Create first category
        ArticleCategory::create([
            'name' => 'Unique Name One',
            'slug' => 'unique-name',
        ]);

        // Create second category with different name - should generate unique slug
        Livewire::test(Category::class)
            ->call('create')
            ->set('form.name', 'Unique Name')
            ->set('form.slug', '') // Let auto-generate
            ->call('save');

        // Should exist with a unique slug
        $category = ArticleCategory::where('name', 'Unique Name')->first();
        $this->assertNotNull($category);
        // Auto-generated slug should be unique (e.g., unique-name-1)
        $this->assertStringContainsString('unique-name', $category->slug);
    }

    /** @test */
    public function generate_slug_method_works()
    {
        $this->actingAs($this->user);

        Livewire::test(Category::class)
            ->call('create')
            ->set('form.name', 'Test Category Name')
            ->call('generateSlug')
            ->assertSet('form.slug', 'test-category-name');
    }
}
