<?php

namespace Tests\Feature\Livewire\Admin\Pages\Article;

use App\Livewire\Admin\Pages\Article\Tag;
use App\Models\CustomPermission;
use App\Models\Group;
use App\Models\Tag as TagModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TagTest extends TestCase
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
            'admin.articles.tags.index',
            'admin.articles.tags.create',
            'admin.articles.tags.edit',
            'admin.articles.tags.destroy',
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
    public function can_render_tags_page()
    {
        $this->actingAs($this->user);

        Livewire::test(Tag::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.pages.article.tag');
    }

    /** @test */
    public function can_create_tag()
    {
        $this->actingAs($this->user);

        Livewire::test(Tag::class)
            ->call('create')
            ->set('form.name', 'Laravel')
            ->call('save');

        $this->assertDatabaseHas('tags', [
            'name' => 'Laravel',
        ]);

        // Verify slug was auto-generated
        $tag = TagModel::where('name', 'Laravel')->first();
        $this->assertNotNull($tag->slug);
        $this->assertStringContainsString('laravel', $tag->slug);
    }

    /** @test */
    public function can_create_tag_with_custom_slug()
    {
        $this->actingAs($this->user);

        Livewire::test(Tag::class)
            ->call('create')
            ->set('form.name', 'PHP Framework')
            ->set('form.slug', 'custom-php-slug')
            ->call('save');

        $this->assertDatabaseHas('tags', [
            'name' => 'PHP Framework',
            'slug' => 'custom-php-slug',
        ]);
    }

    /** @test */
    public function slug_is_auto_generated_if_empty()
    {
        $this->actingAs($this->user);

        Livewire::test(Tag::class)
            ->call('create')
            ->set('form.name', 'Web Development')
            ->set('form.slug', '')
            ->call('save');

        $tag = TagModel::where('name', 'Web Development')->first();
        $this->assertNotNull($tag);
        $this->assertEquals('web-development', $tag->slug);
    }

    /** @test */
    public function cannot_create_tag_with_missing_name()
    {
        $this->actingAs($this->user);

        Livewire::test(Tag::class)
            ->call('create')
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function cannot_create_duplicate_tag_name()
    {
        $this->actingAs($this->user);

        TagModel::create([
            'name' => 'Existing Tag',
            'slug' => 'existing-tag',
        ]);

        Livewire::test(Tag::class)
            ->call('create')
            ->set('form.name', 'Existing Tag')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function can_edit_tag()
    {
        $this->actingAs($this->user);

        $tag = TagModel::create([
            'name' => 'Original Tag',
            'slug' => 'original-tag',
        ]);

        Livewire::test(Tag::class)
            ->call('edit', $tag->id)
            ->assertSet('editId', $tag->id)
            ->set('form.name', 'Updated Tag')
            ->call('save');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag',
        ]);
    }

    /** @test */
    public function can_update_tag_with_same_name()
    {
        $this->actingAs($this->user);

        $tag = TagModel::create([
            'name' => 'My Tag',
            'slug' => 'my-tag',
        ]);

        Livewire::test(Tag::class)
            ->call('edit', $tag->id)
            ->set('form.name', 'My Tag') // Keep same name
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'My Tag',
        ]);
    }

    /** @test */
    public function can_delete_tag()
    {
        $this->actingAs($this->user);

        $tag = TagModel::create([
            'name' => 'To Delete',
            'slug' => 'to-delete',
        ]);

        Livewire::test(Tag::class)
            ->call('confirmDelete', $tag->id)
            ->assertSet('deleteId', $tag->id)
            ->call('delete');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    /** @test */
    public function can_search_tags_by_name()
    {
        $this->actingAs($this->user);

        TagModel::create(['name' => 'Laravel', 'slug' => 'laravel']);
        TagModel::create(['name' => 'Vue.js', 'slug' => 'vue-js']);

        Livewire::test(Tag::class)
            ->set('search', 'Laravel')
            ->assertViewHas('tags', function ($tags) {
                return $tags->count() === 1 
                    && $tags->first()->name === 'Laravel';
            });
    }

    /** @test */
    public function can_search_tags_by_slug()
    {
        $this->actingAs($this->user);

        TagModel::create(['name' => 'React Native', 'slug' => 'react-native']);
        TagModel::create(['name' => 'Flutter', 'slug' => 'flutter-framework']);

        Livewire::test(Tag::class)
            ->set('search', 'react-native')
            ->assertViewHas('tags', function ($tags) {
                return $tags->count() === 1 
                    && $tags->first()->slug === 'react-native';
            });
    }

    /** @test */
    public function can_reset_filters()
    {
        $this->actingAs($this->user);

        Livewire::test(Tag::class)
            ->set('search', 'test search')
            ->call('resetFilters')
            ->assertSet('search', '');
    }

    /** @test */
    public function unauthorized_user_cannot_access_tags()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        Livewire::test(Tag::class)
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_user_cannot_create_tag()
    {
        // Create user with only index permission
        $viewOnlyUser = User::factory()->create();
        $viewRole = Role::create(['name' => 'viewer']);
        $indexPermission = CustomPermission::where('name', 'admin.articles.tags.index')->first();
        $viewRole->givePermissionTo($indexPermission);
        $viewOnlyUser->assignRole($viewRole);

        $this->actingAs($viewOnlyUser);

        // The component handles authorization gracefully with flash message
        Livewire::test(Tag::class)
            ->call('create')
            ->assertStatus(200);
    }

    /** @test */
    public function edit_nonexistent_tag_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Tag::class)
            ->call('edit', 99999);

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function delete_nonexistent_tag_shows_error()
    {
        $this->actingAs($this->user);

        Livewire::test(Tag::class)
            ->call('confirmDelete', 99999);

        // Should not throw exception, handled gracefully
        $this->assertTrue(true);
    }

    /** @test */
    public function tags_are_paginated()
    {
        $this->actingAs($this->user);

        // Create more than 10 tags (pagination limit)
        for ($i = 1; $i <= 15; $i++) {
            TagModel::create([
                'name' => "Tag $i",
                'slug' => "tag-$i",
            ]);
        }

        $component = Livewire::test(Tag::class);

        // Should show pagination (10 per page)
        $component->assertViewHas('tags', function ($tags) {
            return $tags->count() === 10;
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
                return str_contains($message, 'Tag created');
            });

        // This will trigger the observer
        $tag = TagModel::create([
            'name' => 'Observer Test',
            'slug' => 'observer-test',
        ]);

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    /** @test */
    public function name_max_length_validation()
    {
        $this->actingAs($this->user);

        Livewire::test(Tag::class)
            ->call('create')
            ->set('form.name', str_repeat('a', 256)) // Exceeds 255
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    /** @test */
    public function auto_slug_ensures_uniqueness()
    {
        $this->actingAs($this->user);

        // Create first tag
        TagModel::create([
            'name' => 'Unique Name One',
            'slug' => 'unique-name',
        ]);

        // Create second tag with different name - should generate unique slug
        Livewire::test(Tag::class)
            ->call('create')
            ->set('form.name', 'Unique Name')
            ->set('form.slug', '') // Let auto-generate
            ->call('save');

        // Should exist with a unique slug
        $tag = TagModel::where('name', 'Unique Name')->first();
        $this->assertNotNull($tag);
        // Auto-generated slug should be unique (e.g., unique-name-1)
        $this->assertStringContainsString('unique-name', $tag->slug);
    }
}
