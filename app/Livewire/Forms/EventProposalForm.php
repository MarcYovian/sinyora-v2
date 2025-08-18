<?php

namespace App\Livewire\Forms;

use App\Exceptions\ScheduleConflictException;
use App\Services\EventCreationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class EventProposalForm extends Form
{
    public string $guestName = '';
    public string $guestEmail = '';
    public string $guestPhone = '';
    public string $name = '';
    public string $description = '';
    public ?int $event_category_id = null;
    public ?int $organization_id = null;
    public ?string $datetime_start = '';
    public ?string $datetime_end = '';
    public array $locations = [];
    public bool $enableBorrowing = false;
    public array $assets = [];
    public ?string $borrowingNotes = null;

    public function rules(): array
    {
        return [
            'guestName' => ['required', 'string', 'max:255'],
            'guestEmail' => ['required', 'email', 'max:255'],
            'guestPhone' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'datetime_start' => ['required', 'dateformat:Y-m-d\TH:i', 'after_or_equal:today', 'before_or_equal:datetime_end'],
            'datetime_end' => ['required', 'dateformat:Y-m-d\TH:i', 'after_or_equal:datetime_start'],
            'locations' => ['required', 'array', 'min:1'],
            'locations.*' => ['exists:locations,id'],
            'enableBorrowing' => ['boolean'],
            'assets' => [ValidationRule::requiredIf($this->enableBorrowing), 'array'],
            'assets.*.asset_id' => ['required_with:assets', 'exists:assets,id'],
            'assets.*.quantity' => ['required_with:assets', 'integer', 'min:1'],
            'borrowingNotes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'guestName.required' => 'Nama tamu wajib diisi.',
            'guestEmail.required' => 'Email tamu wajib diisi.',
            'guestPhone.required' => 'Nomor telepon tamu wajib diisi.',
            'name.required' => 'Nama acara wajib diisi.',
            'description.required' => 'Deskripsi acara wajib diisi.',
            'event_category_id.required' => 'Kategori acara wajib dipilih.',
            'organization_id.required' => 'Organisasi wajib dipilih.',
            'datetime_start.required' => 'Tanggal mulai acara wajib diisi.',
            'datetime_start.dateformat' => 'Format tanggal mulai harus YYYY-MM-DDTHH:MM.',
            'datetime_start.after_or_equal' => 'Tanggal mulai acara harus setelah tanggal sekarang.',
            'datetime_start.before_or_equal' => 'Tanggal mulai acara harus sebelum atau sama dengan tanggal akhir.',
            'datetime_end.required' => 'Tanggal akhir acara wajib diisi.',
            'datetime_end.dateformat' => 'Format tanggal akhir harus YYYY-MM-DDTHH:MM.',
            'datetime_end.after_or_equal' => 'Tanggal akhir acara harus setelah tanggal mulai.',
            'locations.required' => 'Lokasi acara wajib dipilih.',
            'locations.array' => 'Lokasi acara harus berupa array.',
            'locations.min' => 'Setidaknya satu lokasi harus dipilih.',
            'locations.*.exists' => 'Lokasi yang dipilih tidak valid.',
        ];
    }

    public function store()
    {
        $validated = $this->validate();

        try {
            app(EventCreationService::class)->createEventForGuest($validated);
        } catch (ScheduleConflictException $e) {
            throw ValidationException::withMessages(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage());
            throw ValidationException::withMessages(['error' => __('Failed to create event. Please try again.')]);
        }
    }
}
