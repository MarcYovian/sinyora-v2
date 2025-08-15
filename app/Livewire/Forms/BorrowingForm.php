<?php

namespace App\Livewire\Forms;

use App\Enums\BorrowingStatus;
use App\Models\Borrowing;
use App\Repositories\Eloquent\EloquentActivityRepository;
use App\Repositories\Eloquent\EloquentBorrowingRepository;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Rules\AssetAvailability;
use App\Rules\ValidBorrowingPeriod;
use App\Services\BorrowingManagementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class BorrowingForm extends Form
{
    protected $borrowingManagementService;
    public ?Borrowing $borrowing = null;

    public array $assets = [];
    public $start_datetime;
    public $end_datetime;
    public string $notes = '';
    public string $borrower = '';
    public string $borrower_phone = '';
    public ?string $borrowable_type = null;
    public ?int $borrowable_id = null;
    public string $activity_name = '';
    public string $activity_location = '';

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
            'borrowable_type' => ['required', Rule::in(['event', 'activity'])],
            'borrowable_id' => [
                Rule::requiredIf($this->borrowable_type === 'event'),
                Rule::when($this->borrowable_type === 'event', [
                    Rule::exists('events', 'id'),
                ]),
            ],
            'activity_name' => [
                Rule::requiredIf($this->borrowable_type === 'activity'),
                Rule::when($this->borrowable_type === 'activity', [
                    'string',
                    'max:255',
                ]),
            ],
            'activity_location' => ['nullable', 'string', 'max:255'],
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
            'borrowable_type.required' => 'Tipe peminjaman harus diisi.',
            'borrowable_type.in' => 'Tipe peminjaman tidak valid.',
            'borrowable_id.required_if' => 'ID peminjaman harus diisi jika tipe peminjaman adalah event.',
            'borrowable_id.exists' => 'ID peminjaman tidak valid.',
            'activity_name.required_if' => 'Nama aktivitas harus diisi jika tipe peminjaman adalah aktivitas.',
            'activity_name.string' => 'Nama aktivitas harus berupa string.',
            'activity_name.max' => 'Nama aktivitas tidak boleh lebih dari 255 karakter.',
            'activity_location.string' => 'Lokasi aktivitas harus berupa string.',
            'activity_location.max' => 'Lokasi aktivitas tidak boleh lebih dari 255 karakter.',
        ];
    }

    public function __construct(\Livewire\Component $component, $propertyName)
    {
        parent::__construct($component, $propertyName);

        $this->borrowingManagementService = new BorrowingManagementService(
            new EloquentBorrowingRepository(),
            new EloquentEventRepository(),
            new EloquentActivityRepository()
        );
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
        $validated = $this->validate();

        try {
            $this->borrowingManagementService->createNewBorrowing($validated);
            $this->reset();
        } catch (\Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage());
            throw ValidationException::withMessages(['error' => __('Failed to create event. Please try again.')]);
        }
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

    public function reject($rejectId)
    {
        $this->borrowing = Borrowing::find($rejectId);

        if (!$this->borrowing) {
            $this->addError('borrowing', 'Borrowing not found.');
            return;
        }

        if ($this->borrowing->status !== BorrowingStatus::PENDING) {
            $this->addError('borrowing', 'Borrowing status is not pending.');
            return;
        }

        $this->borrowing->update([
            'status' => BorrowingStatus::REJECTED,
        ]);

        $this->reset();
    }
}
