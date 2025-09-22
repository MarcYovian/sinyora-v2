<?php

namespace Tests\Feature\Livewire\Admin\Pages;

use App\Livewire\Admin\Pages\EventCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_event_category()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $permission = Permission::create(['name' => 'admin.event-categories.create', 'route_name' => 'admin.event-categories.create']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->actingAs($user);

        Livewire::test(EventCategory::class)
            ->set('form.name', 'New Category')
            ->set('form.color', '#FF0000')
            ->set('form.is_active', true)
            ->call('save');

        $this->assertDatabaseHas('event_categories', [
            'name' => 'New Category',
            'color' => '#FF0000',
            'is_active' => true,
        ]);
    }
}
