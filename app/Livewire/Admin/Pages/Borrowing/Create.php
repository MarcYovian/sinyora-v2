<?php

namespace App\Livewire\Admin\Pages\Borrowing;

use App\Enums\BorrowingStatus;
use App\Livewire\Admin\Pages\User;
use App\Livewire\Forms\BorrowingForm;
use App\Models\Asset;
use App\Models\Borrowing as ModelsBorrowing;
use App\Models\User as ModelsUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    #[Layout('layouts.app')]

    public BorrowingForm $form;

    public function mount()
    {
        $this->authorize('access', 'admin.asset-borrowings.create');

        $this->form->reset();
    }

    public function addAsset()
    {
        if ($this->form->start_datetime && $this->form->end_datetime) {
            $this->form->assets[] = [
                'asset_id' => null,
                'quantity' => 1
            ];
        } else {
            toastr()->error('Silahkan isi tanggal mulai dan selesai terlebih dahulu.');
        }
    }

    public function removeAsset($index)
    {
        unset($this->form->assets[$index]);
        $this->form->assets = array_values($this->form->assets);
    }

    #[Computed]
    public function availableAssets()
    {
        $assets = $assets = Asset::query()
            ->where('is_active', true)
            ->with(['borrowings' => function ($query) {
                $query->where('status', BorrowingStatus::APPROVED)
                    ->where(function ($q) {
                        $q->whereBetween('start_datetime', [$this->form->start_datetime, $this->form->end_datetime])
                            ->orWhereBetween('end_datetime', [$this->form->start_datetime, $this->form->end_datetime])
                            ->orWhere(function ($sub) {
                                $sub->where('start_datetime', '<=', $this->form->start_datetime)
                                    ->where('end_datetime', '>=', $this->form->end_datetime);
                            });
                    });
            }])
            ->get()
            ->map(function ($asset) {
                // Sum dari pivot table 'quantity'
                $asset->borrowed_quantity = $asset->borrowings->sum(function ($borrowing) {
                    return $borrowing->pivot->quantity ?? 0;
                });

                return $asset;
            });

        return $assets;
    }

    #[Computed]
    public function selectedAssetIds()
    {
        return collect($this->form->assets)
            ->pluck('asset_id')
            ->filter()
            ->toArray();
    }

    public function store()
    {
        $this->authorize('access', 'admin.asset-borrowings.create');

        $this->validate();

        DB::transaction(function () {
            $user = ModelsUser::find(Auth::id());

            $borrowing = new ModelsBorrowing([
                'start_datetime' => $this->form->start_datetime,
                'end_datetime' => $this->form->end_datetime,
                'notes' => $this->form->notes,
                'borrower' => $this->form->borrower,
                'borrower_phone' => $this->form->borrower_phone,
                'status' => BorrowingStatus::PENDING
            ]);

            $user->borrowings()->save($borrowing);

            $borrowing->assets()->sync($this->form->assets);

            toastr()->success('Borrowing berhasil disimpan');
        });

        return redirect()->route('admin.asset-borrowings.index');
    }

    public function cancel()
    {
        $this->form->reset();
    }
    public function render()
    {
        $this->authorize('access', 'admin.asset-borrowings.create');

        return view('livewire.admin.pages.borrowing.create');
    }
}
