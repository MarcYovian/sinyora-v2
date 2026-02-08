<?php

namespace App\Livewire\Admin\Pages\Borrowing;

use App\Enums\BorrowingStatus;
use App\Livewire\Forms\BorrowingForm;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\Event;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    #[Layout('layouts.app')]

    public BorrowingForm $form;

    public string $assetSearch = '';

    public string $correlationId = '';

    /**
     * Mount the component with an existing borrowing.
     */
    public function mount(Borrowing $borrowing): void
    {
        $this->correlationId = Str::uuid()->toString();

        $this->authorize('access', 'admin.asset-borrowings.edit');

        $this->form->setBorrowing($borrowing);

        Log::info('Edit Borrowing component mounted', [
            'borrowing_id' => $borrowing->id,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);
    }

    /**
     * Open asset selection modal if datetime is set.
     */
    public function openAssetModal(): void
    {
        if ($this->form->start_datetime && $this->form->end_datetime) {
            $this->dispatch('open-modal', 'asset-selection-modal');
        } else {
            flash()->error('Silahkan isi tanggal mulai dan selesai terlebih dahulu.');
        }
    }

    /**
     * Add asset (opens modal if datetime is valid, otherwise shows error).
     */
    public function addAsset(): void
    {
        if ($this->form->start_datetime && $this->form->end_datetime) {
            $this->dispatch('open-modal', 'asset-selection-modal');
        } else {
            flash()->error('Silahkan isi tanggal mulai dan selesai terlebih dahulu.');
        }
    }

    /**
     * Select an asset from the modal.
     */
    public function selectAsset(int $assetId): void
    {
        // Check if asset is already in the list
        $existingIndex = collect($this->form->assets)->search(function ($item) use ($assetId) {
            return $item['asset_id'] === $assetId;
        });

        if ($existingIndex !== false) {
            // Increment quantity if asset already exists
            $this->form->assets[$existingIndex]['quantity']++;

            Log::debug('Asset quantity incremented', [
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

            Log::debug('Asset added to borrowing', [
                'asset_id' => $assetId,
                'correlation_id' => $this->correlationId,
            ]);
        }

        flash()->success('Aset berhasil ditambahkan.');
    }

    /**
     * Remove an asset from the list.
     */
    public function removeAsset(int $index): void
    {
        $assetId = $this->form->assets[$index]['asset_id'] ?? null;

        unset($this->form->assets[$index]);
        $this->form->assets = array_values($this->form->assets);

        Log::debug('Asset removed from borrowing', [
            'asset_id' => $assetId,
            'index' => $index,
            'correlation_id' => $this->correlationId,
        ]);

        flash()->success('Aset berhasil dihapus.');
    }

    /**
     * Get available assets based on the borrowing period.
     * Excludes currently approved borrowings for the same period.
     */
    #[Computed]
    public function availableAssets(): Collection
    {
        // Only run query if date range is valid
        if (empty($this->form->start_datetime) || empty($this->form->end_datetime)) {
            return collect();
        }

        return Asset::query()
            ->select(['id', 'name', 'code', 'quantity', 'is_active', 'asset_category_id', 'image'])
            ->where('is_active', true)
            ->when($this->assetSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->assetSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->assetSearch . '%');
            })
            ->withCount(['borrowings as borrowed_quantity' => function ($query) {
                $query->where('status', 'approved')
                    ->where('borrowings.id', '!=', $this->form->borrowing?->id) // Exclude current borrowing
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
     * Get IDs of assets already selected in the form.
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
     * Get details of selected assets (pre-loaded to avoid N+1).
     * Includes borrowed_quantity for the current period to calculate max quantity.
     */
    #[Computed]
    public function selectedAssetsDetails(): Collection
    {
        $assetIds = collect($this->form->assets)->pluck('asset_id')->filter()->toArray();

        if (empty($assetIds)) {
            return collect();
        }

        return Asset::select(['id', 'name', 'code', 'quantity', 'image'])
            ->whereIn('id', $assetIds)
            ->withCount(['borrowings as borrowed_quantity' => function ($query) {
                $query->where('status', 'approved')
                    ->where('borrowings.id', '!=', $this->form->borrowing?->id)
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
            ->get()
            ->keyBy('id');
    }

    /**
     * Save the updated borrowing.
     */
    public function save(): mixed
    {
        Log::info('Updating borrowing', [
            'borrowing_id' => $this->form->borrowing?->id,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.asset-borrowings.edit');

            $this->form->update();

            flash()->success('Borrowing berhasil diperbarui.');

            Log::info('Borrowing updated successfully', [
                'borrowing_id' => $this->form->borrowing?->id,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);

            return redirect()->route('admin.asset-borrowings.index');
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk mengubah peminjaman.');
            Log::warning('Unauthorized borrowing update attempt', [
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
            return null;
        } catch (ValidationException $e) {
            Log::warning('Borrowing update validation failed', [
                'borrowing_id' => $this->form->borrowing?->id,
                'errors' => $e->errors(),
                'correlation_id' => $this->correlationId,
            ]);
            throw $e;
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan saat memperbarui peminjaman. #{$this->correlationId}");
            Log::error('Borrowing update failed', [
                'borrowing_id' => $this->form->borrowing?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
            return null;
        }
    }

    /**
     * Cancel and reset form.
     */
    public function cancel(): void
    {
        $this->form->resetForm();

        Log::debug('Edit form reset', [
            'correlation_id' => $this->correlationId,
        ]);
    }

    /**
     * Get upcoming events for the borrowable type selection.
     * An event is "upcoming" if it has at least one recurrence >= today.
     */
    #[Computed]
    public function events(): Collection
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
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.asset-borrowings.edit');

        return view('livewire.admin.pages.borrowing.edit');
    }
}
