<?php

namespace App\Livewire\Forms;

use App\Enums\BorrowingStatus;
use App\Models\Borrowing;
use App\Rules\AssetAvailability;
use App\Rules\ValidBorrowingPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class BorrowingForm extends Form
{
    public ?Borrowing $borrowing = null;

    public array $assets = [];
    public $start_datetime;
    public $end_datetime;
    public string $notes = '';
    public string $borrower = '';
    public string $borrower_phone = '';

    public function rules()
    {
        return [
            'assets' => ['required', 'array', 'min:1'],
            'assets.*.asset_id' => [
                'required',
                'exists:assets,id',
                function ($attribute, $value, $fail) {
                    $asset = \App\Models\Asset::find($value);
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

    public function setBorrowing(?Borrowing $borrowing)
    {
        $this->borrowing = $borrowing->load('event');
        if ($borrowing) {
            $this->assets = $borrowing->assets->map(function ($asset) {
                return [
                    'asset_id' => $asset->id,
                    'quantity' => $asset->pivot->quantity,
                ];
            })->toArray();
            $this->start_datetime = $borrowing->start_datetime->format('Y-m-d\TH:i');
            $this->end_datetime = $borrowing->end_datetime->format('Y-m-d\TH:i');
            $this->notes = $borrowing->notes ?? '';
            $this->borrower = $borrowing->borrower;
            $this->borrower_phone = $borrowing->borrower_phone;
        }
    }

    public function store()
    {
        $this->validate();

        $borrowing = Borrowing::create([
            'start_datetime' => $this->start_datetime,
            'end_datetime' => $this->end_datetime,
            'notes' => $this->notes,
            'borrower' => $this->borrower,
            'borrower_phone' => $this->borrower_phone,
            'status' => BorrowingStatus::PENDING
        ]);

        $borrowing->assets()->sync($this->assets);

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->borrowing->update([
            'start_datetime' => $this->start_datetime,
            'end_datetime' => $this->end_datetime,
            'notes' => $this->notes,
            'borrower' => $this->borrower,
            'borrower_phone' => $this->borrower_phone,
        ]);

        $this->borrowing->assets()->sync($this->assets);

        $this->reset();
    }

    public function destroy()
    {
        if ($this->borrowing) {
            $this->borrowing->assets()->detach();
            $this->borrowing->delete();
        }

        $this->reset();
    }

    public function approve($approveId)
    {
        $this->borrowing = Borrowing::with('assets')->find($approveId);

        if (!$this->borrowing) {
            $this->addError('borrowing', 'Borrowing not found.');
            return;
        }

        if ($this->borrowing->status !== BorrowingStatus::PENDING) {
            $this->addError('borrowing', 'Borrowing status is not pending.');
            return;
        }

        foreach ($this->borrowing->assets as $asset) {
            if (!$asset->is_active) {
                $this->addError('borrowing', "Asset {$asset->name} is not active.");
                return;
            }

            // Jalankan manual validasi AssetAvailability
            $rule = new AssetAvailability(
                assetId: $asset->id,
                startDate: $this->borrowing->start_datetime,
                endDate: $this->borrowing->end_datetime,
                excludeBorrowingId: $this->borrowing->id
            );

            // Gunakan closure fail palsu
            $errors = [];
            $rule->validate('assets.quantity', $asset->pivot->quantity, function ($message) use (&$errors) {
                $errors[] = $message;
            });

            if (!empty($errors)) {
                $this->addError('borrowing', "Asset {$asset->name}: {$errors[0]}");
                return;
            }
        }

        $this->borrowing->update([
            'status' => BorrowingStatus::APPROVED,
        ]);

        $this->reset();
    }
}
