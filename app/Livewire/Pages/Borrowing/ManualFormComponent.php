<?php

namespace App\Livewire\Pages\Borrowing;

use App\Enums\BorrowingStatus;
use App\Livewire\Forms\BorrowingForm;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\GuestSubmitter;
use App\Rules\AssetAvailability;
use App\Rules\ValidBorrowingPeriod;
use Exception;
use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManualFormComponent extends Component
{
    public ?Borrowing $borrowing = null;
    public $guest_name;
    public $guest_email;
    public $guest_phone;
    public array $assets = [];
    public $start_datetime;
    public $end_datetime;
    public string $notes = '';
    public string $borrower = '';
    public string $borrower_phone = '';

    public function rules()
    {
        return [
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
            'assets' => ['required', 'array', 'min:1'],
            'assets.*.asset_id' => [
                'required',
                'exists:assets,id',
                function ($attribute, $value, $fail) {
                    $asset = Asset::find($value);
                    if ($asset && !$asset->is_active) {
                        $fail('Asset tidak aktif untuk dipinjam.');
                    }
                }
            ],
            'assets.*.quantity' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1]; // Ambil index dari attribute name
                    // dd($this->assets[$index]);
                    $assetId = $this->assets[$index]['asset_id'] ?? null;

                    if (!$assetId) {
                        $fail('Asset ID tidak valid.');
                        return;
                    }

                    (new AssetAvailability(
                        assetId: $assetId,
                        startDate: $this->start_datetime,
                        endDate: $this->end_datetime,
                        excludeBorrowingId: $this->borrowing?->id
                    ))->validate($attribute, $value, $fail);
                }
            ],
            'start_datetime' => [
                'required',
                'date',
                'after_or_equal:today',
                new ValidBorrowingPeriod(
                    startDate: $this->start_datetime,
                    endDate: $this->end_datetime
                )
            ],
            'end_datetime' => [
                'required',
                'date',
                'after:start_datetime',
                function ($attribute, $value, $fail) {
                    (new ValidBorrowingPeriod(
                        startDate: $this->start_datetime,
                        endDate: $value
                    ))->validate($attribute, $value, $fail);
                }
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'borrower' => ['required', 'string', 'max:100'],
            'borrower_phone' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages()
    {
        return [
            'guest_name.required' => 'Nama lengkap harus diisi.',
            'guest_email.required' => 'Alamat email harus diisi.',
            'guest_email.email' => 'Format alamat email tidak valid.',
            'guest_phone.required' => 'Nomor telepon harus diisi.',
            'assets.required' => 'Setidaknya satu aset harus dipilih.',
            'assets.*.asset_id.required' => 'ID aset harus diisi.',
            'assets.*.asset_id.exists' => 'Aset yang dipilih tidak valid.',
            'assets.*.quantity.required' => 'Jumlah aset harus diisi.',
            'assets.*.quantity.numeric' => 'Jumlah aset harus berupa angka.',
            'start_datetime.required' => 'Tanggal mulai harus diisi.',
            'start_datetime.date' => 'Tanggal mulai tidak valid.',
            'start_datetime.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_datetime.required' => 'Tanggal selesai harus diisi.',
            'end_datetime.date' => 'Tanggal selesai tidak valid.',
            'end_datetime.after' => 'Tanggal selesai harus setelah tanggal mulai.',
            'notes.max' => 'Catatan tidak boleh lebih dari 1000 karakter.',
            'borrower.required' => 'Nama peminjam harus diisi.',
            'borrower_phone.required' => 'Nomor telepon peminjam harus diisi.',
        ];
    }

    public function addAsset()
    {
        if ($this->start_datetime && $this->end_datetime) {
            $this->assets[] = [
                'asset_id' => null,
                'quantity' => 1
            ];
        } else {
            toastr()->error('Silahkan isi tanggal mulai dan selesai terlebih dahulu.');
        }
    }

    public function removeAsset($index)
    {
        unset($this->assets[$index]);
        $this->assets = array_values($this->assets);
    }

    #[Computed]
    public function availableAssets()
    {
        $assets = $assets = Asset::query()
            ->where('is_active', true)
            ->with(['borrowings' => function ($query) {
                $query->where('status', BorrowingStatus::APPROVED)
                    ->where(function ($q) {
                        $q->whereBetween('start_datetime', [$this->start_datetime, $this->end_datetime])
                            ->orWhereBetween('end_datetime', [$this->start_datetime, $this->end_datetime])
                            ->orWhere(function ($sub) {
                                $sub->where('start_datetime', '<=', $this->start_datetime)
                                    ->where('end_datetime', '>=', $this->end_datetime);
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

    public function save()
    {
        $this->borrower = $this->guest_name;
        $this->borrower_phone = $this->guest_phone;
        $this->validate();
        // dd($this->assets, $this->start_datetime, $this->end_datetime, $this->notes, $this->guest_name, $this->guest_email, $this->guest_phone, $this->borrower, $this->borrower_phone);
        try {
            \Illuminate\Support\Facades\DB::transaction(function () {
                $guest = GuestSubmitter::firstOrCreate(
                    ['email' => $this->guest_email],
                    [
                        'name' => $this->guest_name,
                        'phone_number' => $this->guest_phone,
                    ]
                );

                $borrowing = new Borrowing([
                    'start_datetime' => $this->start_datetime,
                    'end_datetime' => $this->end_datetime,
                    'notes' => $this->notes,
                    'borrower' => $this->borrower,
                    'borrower_phone' => $this->borrower_phone,
                    'status' => BorrowingStatus::PENDING
                ]);

                $guest->borrowings()->save($borrowing);
                
                $borrowing->assets()->sync($this->assets);

            });

            toastr()->success('Permintaan peminjaman berhasil diajukan.');

            $this->dispatch('close-modal', 'proposal-modal');
        } catch (Exception  $e) {
            Log::error('Gagal mengajukan peminjaman: ' . $e->getMessage());

            toastr()->error('Terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi.');
        }
    }

    #[Computed]
    public function selectedAssetIds()
    {
        return collect($this->assets)
            ->pluck('asset_id')
            ->filter()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.pages.borrowing.manual-form-component');
    }
}
