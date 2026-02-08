<?php

namespace Tests\Feature\Livewire\Admin\Pages\Borrowing;

use App\Enums\BorrowingStatus;
use App\Livewire\Admin\Pages\Borrowing\Create;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Borrowing;
use App\Models\CustomPermission;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BorrowingCreateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $unauthorizedUser;
    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions using CustomPermission (required for Gate::define in AppServiceProvider)
        CustomPermission::create([
            'name' => 'access admin.asset-borrowings.create',
            'guard_name' => 'web',
            'route_name' => 'admin.asset-borrowings.create',
        ]);
        CustomPermission::create([
            'name' => 'access admin.asset-borrowings.index',
            'guard_name' => 'web',
            'route_name' => 'admin.asset-borrowings.index',
        ]);

        // Create role with permission
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $role->givePermissionTo(['access admin.asset-borrowings.create', 'access admin.asset-borrowings.index']);

        // Create authorized user
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Create unauthorized user
        $this->unauthorizedUser = User::factory()->create();

        // Create asset category
        $category = AssetCategory::factory()->create();

        // Create test asset
        $this->asset = Asset::factory()->create([
            'asset_category_id' => $category->id,
            'quantity' => 10,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function can_render_create_borrowing_page()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->assertStatus(200)
            ->assertSee('Buat Peminjaman Baru');
    }

    /** @test */
    public function unauthorized_user_cannot_access_create_page()
    {
        $this->actingAs($this->unauthorizedUser);

        Livewire::test(Create::class)
            ->assertForbidden();
    }

    /** @test */
    public function can_add_asset_to_borrowing_list()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->call('selectAsset', $this->asset->id)
            ->assertSet('form.assets', [
                ['asset_id' => $this->asset->id, 'quantity' => 1]
            ]);
    }

    /** @test */
    public function can_increment_asset_quantity_when_selecting_same_asset()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->call('selectAsset', $this->asset->id)
            ->call('selectAsset', $this->asset->id)
            ->assertSet('form.assets.0.quantity', 2);
    }

    /** @test */
    public function can_remove_asset_from_borrowing_list()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->call('selectAsset', $this->asset->id)
            ->assertCount('form.assets', 1)
            ->call('removeAsset', 0)
            ->assertCount('form.assets', 0);
    }

    /** @test */
    public function cannot_open_asset_modal_without_datetime()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->call('openAssetModal')
            ->assertNotDispatched('open-modal');
    }

    /** @test */
    public function can_open_asset_modal_with_valid_datetime()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->call('openAssetModal')
            ->assertDispatched('open-modal', 'asset-selection-modal');
    }

    /** @test */
    public function validation_fails_without_required_fields()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->call('store')
            ->assertHasErrors(['form.assets', 'form.start_datetime', 'form.end_datetime', 'form.borrower', 'form.borrower_phone', 'form.borrowable_type']);
    }

    /** @test */
    public function validation_fails_when_end_datetime_before_start()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(5)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(2)->format('Y-m-d\TH:i'))
            ->set('form.borrower', 'Test Borrower')
            ->set('form.borrower_phone', '08123456789')
            ->set('form.borrowable_type', 'activity')
            ->set('form.activity_name', 'Test Activity')
            ->set('form.assets', [['asset_id' => $this->asset->id, 'quantity' => 1]])
            ->call('store')
            ->assertHasErrors(['form.end_datetime']);
    }

    /** @test */
    public function can_create_borrowing_with_activity_type()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->set('form.borrower', 'Test Borrower')
            ->set('form.borrower_phone', '08123456789')
            ->set('form.borrowable_type', 'activity')
            ->set('form.activity_name', 'Test Activity')
            ->set('form.activity_location', 'Test Location')
            ->set('form.assets', [['asset_id' => $this->asset->id, 'quantity' => 2]])
            ->call('store')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.asset-borrowings.index'));

        $this->assertDatabaseHas('borrowings', [
            'borrower' => 'Test Borrower',
            'borrower_phone' => '08123456789',
            'status' => BorrowingStatus::PENDING,
        ]);

        $this->assertDatabaseHas('activities', [
            'name' => 'Test Activity',
            'location' => 'Test Location',
        ]);
    }

    /** @test */
    public function can_create_borrowing_with_event_type()
    {
        $this->actingAs($this->user);

        $event = Event::factory()->create([
            'start_recurring' => now()->addDays(5),
        ]);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->set('form.borrower', 'Event Borrower')
            ->set('form.borrower_phone', '08199999999')
            ->set('form.borrowable_type', 'event')
            ->set('form.borrowable_id', $event->id)
            ->set('form.assets', [['asset_id' => $this->asset->id, 'quantity' => 1]])
            ->call('store')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.asset-borrowings.index'));

        $this->assertDatabaseHas('borrowings', [
            'borrower' => 'Event Borrower',
            'borrowable_id' => $event->id,
        ]);
    }

    /** @test */
    public function borrowing_creates_database_records()
    {
        $this->actingAs($this->user);

        $this->assertDatabaseCount('borrowings', 0);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->set('form.borrower', 'Log Test Borrower')
            ->set('form.borrower_phone', '08111111111')
            ->set('form.borrowable_type', 'activity')
            ->set('form.activity_name', 'Log Test Activity')
            ->set('form.assets', [['asset_id' => $this->asset->id, 'quantity' => 1]])
            ->call('store')
            ->assertHasNoErrors();

        // Verify borrowing was created
        $this->assertDatabaseCount('borrowings', 1);
        $this->assertDatabaseHas('borrowings', [
            'borrower' => 'Log Test Borrower',
        ]);
    }

    /** @test */
    public function cancel_resets_form_fields()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->set('form.borrower', 'Test Name')
            ->set('form.borrower_phone', '08123456789')
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->assertSet('form.borrower', 'Test Name')
            ->call('cancel')
            ->assertSet('form.assets', []);
    }

    /** @test */
    public function selected_asset_appears_after_selection()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->call('selectAsset', $this->asset->id)
            ->assertSet('form.assets.0.asset_id', $this->asset->id)
            ->assertSet('form.assets.0.quantity', 1);
    }

    /** @test */
    public function borrowing_fails_when_asset_quantity_exceeds_available()
    {
        $this->actingAs($this->user);

        // Create existing approved borrowing that uses some quantity
        $existing = Borrowing::factory()->approved()->create();
        $existing->assets()->attach($this->asset->id, ['quantity' => 8]);

        Livewire::test(Create::class)
            ->set('form.assets', [
                ['asset_id' => $this->asset->id, 'quantity' => 5] // Requesting more than available (10-8=2)
            ])
            ->set('form.start_datetime', now()->addDays(2)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(4)->format('Y-m-d\TH:i'))
            ->set('form.borrower', 'Test')
            ->set('form.borrower_phone', '08123456789')
            ->set('form.borrowable_type', 'activity')
            ->set('form.activity_name', 'Test')
            ->call('store')
            ->assertHasErrors(['form.assets.0.quantity']);
    }

    /** @test */
    public function borrowing_fails_with_inactive_asset()
    {
        $this->actingAs($this->user);

        // Create inactive asset
        $inactiveAsset = Asset::factory()->create([
            'asset_category_id' => AssetCategory::first()->id,
            'quantity' => 10,
            'is_active' => false,
            'created_by' => $this->user->id,
        ]);

        Livewire::test(Create::class)
            ->set('form.assets', [
                ['asset_id' => $inactiveAsset->id, 'quantity' => 1]
            ])
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->set('form.borrower', 'Test')
            ->set('form.borrower_phone', '08123456789')
            ->set('form.borrowable_type', 'activity')
            ->set('form.activity_name', 'Test')
            ->call('store')
            ->assertHasErrors(['form.assets.0.asset_id']);
    }

    /** @test */
    public function component_has_correlation_id()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(Create::class);

        $this->assertNotEmpty($component->get('correlationId'));
    }

    /** @test */
    public function add_asset_method_requires_datetime()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->call('addAsset')
            ->assertNotDispatched('open-modal');
    }

    /** @test */
    public function add_asset_opens_modal_with_datetime()
    {
        $this->actingAs($this->user);

        Livewire::test(Create::class)
            ->set('form.start_datetime', now()->addDays(1)->format('Y-m-d\TH:i'))
            ->set('form.end_datetime', now()->addDays(3)->format('Y-m-d\TH:i'))
            ->call('addAsset')
            ->assertDispatched('open-modal', 'asset-selection-modal');
    }
}
