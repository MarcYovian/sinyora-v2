<?php

namespace App\Livewire\Pages\Event;

use App\Livewire\Forms\EventProposalForm;
use App\Models\Asset;
use App\Models\EventCategory as Category;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManualProposalForm extends Component
{
    public EventProposalForm $form;

    public bool $enableBorrowing = false;

    public string $correlationId = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Save the manual event proposal.
     */
    public function save(): void
    {
        if ($this->form->enableBorrowing) {
            // Filter collection to only include selected assets.
            $selectedAssets = collect($this->form->assets)
                ->where('selected', true);

            // Reformat array to match validation rules.
            $this->form->assets = $selectedAssets->map(function ($data, $assetId) {
                return [
                    'asset_id' => $assetId,
                    'quantity' => $data['quantity'] ?? 1,
                ];
            })->values()->all();
        } else {
            $this->form->assets = [];
        }

        try {
            Log::info('Manual event proposal submission initiated', [
                'guest_email' => $this->form->guestEmail,
                'event_name' => $this->form->name,
                'correlation_id' => $this->correlationId,
            ]);

            $this->form->store();

            flash()->success('Proposal berhasil diajukan.');
            $this->dispatch('close-modal', 'proposal-modal');

            Log::info('Manual event proposal submitted successfully', [
                'guest_email' => $this->form->guestEmail,
                'event_name' => $this->form->name,
                'correlation_id' => $this->correlationId,
            ]);
        } catch (ValidationException $e) {
            flash()->error($e->validator->errors()->first());
        } catch (\Throwable $th) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Manual event proposal submission failed', [
                'guest_email' => $this->form->guestEmail,
                'event_name' => $this->form->name,
                'error' => $th->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        }
    }

    /**
     * Get available assets for borrowing.
     */
    #[Computed]
    public function availableAssets()
    {
        return Asset::active()->where('quantity', '>', 0)->get(['id', 'name', 'quantity']);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.pages.event.manual-proposal-form', [
            'categories' => Cache::remember('event_categories_dropdown', 3600, function () {
                return Category::active()->get(['id', 'name']);
            }),
            'organizations' => Cache::remember('organizations_dropdown', 3600, function () {
                return Organization::active()->get(['id', 'name']);
            }),
            'locations' => Cache::remember('locations_dropdown', 3600, function () {
                return Location::active()->get(['id', 'name', 'description']);
            }),
            'availableAssets' => $this->availableAssets,
        ]);
    }
}
