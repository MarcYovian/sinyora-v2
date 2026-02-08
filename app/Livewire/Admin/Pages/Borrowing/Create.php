<?php

namespace App\Livewire\Admin\Pages\Borrowing;

use App\Livewire\Forms\BorrowingForm;
use App\Models\Asset;
use App\Models\Event;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    #[Layout('layouts.app')]

    public bool $isAssetModalOpen = false;
    public string $assetSearch = '';
    public BorrowingForm $form;
    public string $correlationId = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('access', 'admin.asset-borrowings.create');
        $this->correlationId = Str::uuid()->toString();
        $this->form->reset();

        Log::debug('Borrowing create page mounted', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
        ]);
    }

    /**
     * Validate datetime before adding assets.
     */
    public function addAsset(): void
    {
        if ($this->form->start_datetime && $this->form->end_datetime) {
            $this->openAssetModal();
        } else {
            flash()->error('Silahkan isi tanggal mulai dan selesai terlebih dahulu.');
        }
    }

    /**
     * Open asset selection modal.
     */
    public function openAssetModal(): void
    {
        if (!$this->form->start_datetime || !$this->form->end_datetime) {
            flash()->error('Silahkan isi tanggal mulai dan selesai terlebih dahulu.');
            return;
        }

        $this->dispatch('open-modal', 'asset-selection-modal');
    }

    /**
     * Select an asset to add to borrowing.
     */
    public function selectAsset(int $assetId): void
    {
        // Check if asset is already selected
        $existingIndex = null;
        foreach ($this->form->assets as $index => $asset) {
            if ($asset['asset_id'] == $assetId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // Increment quantity if already exists
            $this->form->assets[$existingIndex]['quantity']++;
            flash()->success('Jumlah aset berhasil ditambahkan.');

            Log::debug('Asset quantity incremented in borrowing', [
                'asset_id' => $assetId,
                'new_quantity' => $this->form->assets[$existingIndex]['quantity'],
                'correlation_id' => $this->correlationId,
            ]);
        } else {
            // Add new asset
            $this->form->assets[] = [
                'asset_id' => $assetId,
                'quantity' => 1
            ];
            flash()->success('Aset berhasil ditambahkan ke daftar.');

            Log::debug('Asset added to borrowing', [
                'asset_id' => $assetId,
                'correlation_id' => $this->correlationId,
            ]);
        }
    }

    /**
     * Remove an asset from the borrowing list.
     */
    public function removeAsset(int $index): void
    {
        $removedAssetId = $this->form->assets[$index]['asset_id'] ?? null;

        unset($this->form->assets[$index]);
        $this->form->assets = array_values($this->form->assets);

        flash()->success('Aset berhasil dihapus dari daftar.');

        Log::debug('Asset removed from borrowing', [
            'asset_id' => $removedAssetId,
            'correlation_id' => $this->correlationId,
        ]);
    }

    /**
     * Get available assets for the selected period.
     */
    #[Computed]
    public function availableAssets()
    {
        // Only run query if datetime period is valid
        if (empty($this->form->start_datetime) || empty($this->form->end_datetime)) {
            return collect();
        }

        return Asset::query()
            ->where('is_active', true)
            ->when($this->assetSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->assetSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->assetSearch . '%');
            })
            ->withCount(['borrowings as borrowed_quantity' => function ($query) {
                $query->where('status', 'approved')
                    ->where(function ($q) {
                        $q->whereBetween('start_datetime', [$this->form->start_datetime, $this->form->end_datetime])
                            ->orWhereBetween('end_datetime', [$this->form->start_datetime, $this->form->end_datetime])
                            ->orWhere(function ($sub) {
                                $sub->where('start_datetime', '<', $this->form->start_datetime)
                                    ->where('end_datetime', '>', $this->form->end_datetime);
                            });
                    })
                    ->select(DB::raw("SUM(asset_borrowing.quantity)"));
            }])
            ->get();
    }

    /**
     * Get IDs of currently selected assets.
     */
    #[Computed]
    public function selectedAssetIds(): array
    {
        return collect($this->form->assets)
            ->pluck('asset_id')
            ->filter()
            ->toArray();
    }

    /**
     * Get selected assets with full details for display.
     */
    #[Computed]
    public function selectedAssetsDetails()
    {
        $assetIds = collect($this->form->assets)->pluck('asset_id')->filter()->toArray();

        if (empty($assetIds)) {
            return collect();
        }

        return Asset::whereIn('id', $assetIds)->get()->keyBy('id');
    }

    /**
     * Get upcoming events for selection.
     * An event is considered "upcoming" if it has at least one recurrence date >= today.
     */
    #[Computed]
    public function events()
    {
        return Event::select(['id', 'name', 'start_recurring'])
            ->whereHas('eventRecurrences', function ($query) {
                $query->where('date', '>=', now()->toDateString());
            })
            ->orderBy('name', 'asc')
            ->limit(50)
            ->get();
    }

    /**
     * Store a new borrowing.
     */
    public function store(): mixed
    {
        Log::info('Borrowing store action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'assets_count' => count($this->form->assets),
        ]);

        try {
            $this->authorize('access', 'admin.asset-borrowings.create');

            $this->form->store();

            flash()->success('Peminjaman berhasil disimpan.');

            Log::info('Borrowing created successfully', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
            ]);

            return redirect()->route('admin.asset-borrowings.index');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized borrowing creation attempt', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
            ]);
            flash()->error('Anda tidak memiliki izin untuk membuat peminjaman.');
            return null;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Borrowing creation failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("Terjadi kesalahan saat menyimpan peminjaman. #{$this->correlationId}");
            return null;
        }
    }

    /**
     * Cancel and reset form.
     */
    public function cancel(): void
    {
        $this->form->resetForm();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.asset-borrowings.create');

        return view('livewire.admin.pages.borrowing.create');
    }
}
