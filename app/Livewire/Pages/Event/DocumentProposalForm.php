<?php

namespace App\Livewire\Pages\Event;

use App\Models\GuestSubmitter;
use App\Services\DocumentManagementService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentProposalForm extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public $attachment = null;

    public string $correlationId = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Validation rules for the document proposal form.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'attachment' => ['required', 'file', 'mimes:pdf,png,jpg', 'max:5120'], // Maks 5MB
        ];
    }

    /**
     * Save the document proposal submitted by a guest.
     */
    public function save(): void
    {
        try {
            $this->validate();

            Log::info('Guest document proposal submission initiated', [
                'guest_email' => $this->email,
                'file_name' => $this->attachment?->getClientOriginalName(),
                'correlation_id' => $this->correlationId,
            ]);

            $guest = GuestSubmitter::firstOrCreate(
                ['email' => $this->email],
                [
                    'name' => $this->name,
                    'phone_number' => $this->phone,
                ]
            );

            app(DocumentManagementService::class)->storeNewDocument($this->attachment, $guest);

            flash()->success('Dokumen proposal berhasil diajukan.');
            $this->dispatch('close-modal', 'proposal-modal');
            $this->reset(['name', 'email', 'phone', 'attachment']);

            Log::info('Guest document proposal submitted successfully', [
                'guest_email' => $this->email,
                'correlation_id' => $this->correlationId,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $th) {
            flash()->error('Terjadi kesalahan yang tidak terduga. Silakan coba lagi.');
            Log::error('Guest document proposal submission failed', [
                'guest_email' => $this->email,
                'error' => $th->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        }
    }

    /**
     * Remove the currently attached document.
     */
    public function removeDocument(): void
    {
        $this->attachment = null;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.pages.event.document-proposal-form');
    }
}
