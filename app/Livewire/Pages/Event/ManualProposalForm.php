<?php

namespace App\Livewire\Pages\Event;

use App\Livewire\Forms\EventProposalForm;
use App\Models\Asset;
use App\Models\EventCategory as Category;
use App\Models\GuestSubmitter;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManualProposalForm extends Component
{
    public EventProposalForm $form;

    public bool $enableBorrowing = false;

    public function save()
    {
        if ($this->form->enableBorrowing) {
            // 1. Filter koleksi untuk hanya mengambil aset yang 'selected' bernilai true.
            $selectedAssets = collect($this->form->assets)
                ->where('selected', true);

            // 2. Ubah format array agar sesuai dengan aturan validasi.
            $this->form->assets = $selectedAssets->map(function ($data, $assetId) {
                return [
                    'asset_id' => $assetId,
                    // Jika user hanya centang tanpa isi kuantitas, default ke 1.
                    'quantity' => $data['quantity'] ?? 1,
                ];
            })->values()->all(); // Reset keys menjadi 0, 1, 2...
        } else {
            $this->form->assets = [];
        }

        try {
            $this->form->store();
            flash()->success('Proposal created successfully');
            $this->dispatch('close-modal', 'proposal-modal');
        } catch (ValidationException $e) {
            flash()->error($e->validator->errors()->first());
        } catch (\Exception $e) {
            // 4. Tangkap error umum lainnya
            flash()->error('Terjadi kesalahan yang tidak terduga.');
            Log::error('Caught Approval Exception in Component: ' . $e->getMessage());
        }
    }

    #[Computed]
    public function availableAssets()
    {
        return Asset::active()->where('quantity', '>', 0)->get();
    }

    public function render()
    {
        return view('livewire.pages.event.manual-proposal-form', [
            'categories' => Category::all(['id', 'name']),
            'organizations' => Organization::all(['id', 'name']),
            'locations' => Location::all(['id', 'name', 'description']),
            'availableAssets' => $this->availableAssets,
        ]);
    }
}
