<?php

namespace Tests\Feature\Livewire;

use App\Enums\BorrowingStatus;
use App\Livewire\Admin\Pages\Borrowing\Create;
use App\Livewire\Forms\BorrowingForm;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\TestCase;

class BorrowingFormTest extends TestCase
{
    use RefreshDatabase;
    public function test_borrowing_fails_when_asset_quantity_exceeds_available()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'), // password
        ]);
        \App\Models\AssetCategory::factory(1)->create();
        $asset = Asset::factory()->create([
            'asset_category_id' => 1,
            'quantity' => 5,
            'is_active' => true,
            'created_by' => $user->id
        ]);

        $existing = Borrowing::factory()->create([
            'status' => BorrowingStatus::APPROVED,
            'start_datetime' => now()->addDays(1),
            'end_datetime' => now()->addDays(5),
        ]);
        $existing->assets()->attach($asset->id, ['quantity' => 3]);

        Log::info($existing->load('assets'));

        Livewire::test(Create::class)
            ->set('form.assets', [
                ['asset_id' => $asset->id, 'quantity' => 3]
            ])
            ->set('form.start_datetime', now()->addDays(2)->toDateTimeString())
            ->set('form.end_datetime', now()->addDays(4)->toDateTimeString())
            ->call('store')
            ->assertHasErrors(['form.assets.0.quantity'])
            ->assertSee('Jumlah tidak tersedia');
    }

    public function test_borrowing_fails_when_start_is_after_end()
    {
        Livewire::test(BorrowingForm::class)
            ->set('form.start_datetime', now()->addDays(5)->toDateTimeString())
            ->set('form.end_datetime', now()->addDays(3)->toDateTimeString())
            ->call('form.store')
            ->assertHasErrors(['form.start_datetime'])
            ->assertSee('Tanggal mulai harus sebelum tanggal selesai');
    }

    public function test_borrowing_fails_when_duration_exceeds_3_months()
    {
        Livewire::test(BorrowingForm::class)
            ->set('form.start_datetime', now()->toDateTimeString())
            ->set('form.end_datetime', now()->addMonths(4)->toDateTimeString())
            ->call('form.store')
            ->assertHasErrors(['form.end_datetime'])
            ->assertSee('Maksimal periode peminjaman adalah 3 bulan');
    }

    public function test_borrowing_fails_when_asset_is_not_active()
    {
        $asset = Asset::factory()->create([
            'quantity' => 10,
            'is_active' => false,
        ]);

        Livewire::test(BorrowingForm::class)
            ->set('form.assets', [
                ['asset_id' => $asset->id, 'quantity' => 1]
            ])
            ->set('form.start_datetime', now()->addDay()->toDateTimeString())
            ->set('form.end_datetime', now()->addDays(2)->toDateTimeString())
            ->call('form.store')
            ->assertHasErrors(['form.assets.0.asset_id'])
            ->assertSee('Asset tidak aktif');
    }

    public function test_borrowing_succeeds_with_valid_data()
    {
        $asset = Asset::factory()->create([
            'quantity' => 10,
            'is_active' => true,
        ]);

        Livewire::test(BorrowingForm::class)
            ->set('form.assets', [
                ['asset_id' => $asset->id, 'quantity' => 2]
            ])
            ->set('form.start_datetime', now()->addDays(1)->toDateTimeString())
            ->set('form.end_datetime', now()->addDays(3)->toDateTimeString())
            ->set('form.notes', 'Unit test borrowing')
            ->call('form.store')
            ->assertHasNoErrors()
            ->assertDispatched('success');
    }
}
