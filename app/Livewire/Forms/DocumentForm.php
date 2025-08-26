<?php

namespace App\Livewire\Forms;

use App\DataTransferObjects\LetterData;
use App\Enums\BorrowingStatus;
use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Livewire\Admin\Pages\User;
use App\Models\Borrowing;
use App\Models\BorrowingDocument;
use App\Models\CustomLocation;
use App\Models\Document;
use App\Models\Event;
use App\Models\EventRecurrence;
use App\Models\InvitationDocument;
use App\Models\LicensingDocument;
use App\Models\User as ModelsUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class DocumentForm extends Form
{
    public array $data = [];

    public array $analysisResult = [];
    public array $editableKegiatan = [];
    public array $editableData = [];

    public function rules()
    {
        return [
            // Validasi untuk tipe dokumen yang diedit
            'analysisResult.type' => 'required|string|in:undangan,peminjaman,perizinan',

            // Validasi data umum yang diedit
            'analysisResult.document_information.city' => 'nullable|string',
            'analysisResult.document_information.document_date' => 'required|string',
            'analysisResult.document_information.document_number' => 'nullable|string',
            'analysisResult.document_information.emitter_email' => 'nullable|string',
            'analysisResult.document_information.emitter_organizations' => 'required|array|min:1',
            'analysisResult.document_information.emitter_organizations.*.name' => 'required|string',
            'analysisResult.document_information.recipients' => 'required|array|min:1',
            'analysisResult.document_information.recipients.*.name' => 'required|string',
            'analysisResult.document_information.recipients.*.position' => 'nullable|string',
            'analysisResult.document_information.subjects' => 'required|array|min:1',
            'analysisResult.document_information.subjects.*' => 'required|string',

            // Validasi untuk setiap kegiatan yang diedit
            'analysisResult.events.*.attendees' => 'nullable|string',
            'analysisResult.events.*.date' => 'required|string',
            'analysisResult.events.*.equipment' => 'nullable|array',
            'analysisResult.events.*.equipment.*.item' => 'nullable|string',
            'analysisResult.events.*.equipment.*.quantity' => 'nullable|string',
            'analysisResult.events.*.eventName' => 'required|string',
            'analysisResult.events.*.location' => 'required|string',
            'analysisResult.events.*.organizers' => 'nullable|array',
            'analysisResult.events.*.organizers.*.name' => 'required|string',
            'analysisResult.events.*.organizers.*.contact' => 'required|string',
            'analysisResult.events.*.schedule' => 'nullable|array',
            'analysisResult.events.*.schedule.*.description' => 'required|string',
            'analysisResult.events.*.schedule.*.duration' => 'nullable|string',
            'analysisResult.events.*.schedule.*.startTime' => 'nullable|string',
            'analysisResult.events.*.schedule.*.endTime' => 'nullable|string',
            'analysisResult.events.*.time' => 'required|string',

            // Validasi untuk penanda tangan (data dinamis di analysisResult)
            'analysisResult.signature_blocks' => 'required|array|min:1',
            'analysisResult.signature_blocks.*.name' => 'required|string',
            'analysisResult.signature_blocks.*.position' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            // Pesan untuk Tipe Dokumen
            'analysisResult.type.required' => 'Tipe dokumen wajib diisi.',
            'analysisResult.type.string' => 'Tipe dokumen harus berupa teks.',
            'analysisResult.type.in' => 'Tipe dokumen harus salah satu dari: undangan, peminjaman, perizinan.',

            // Pesan untuk Informasi Umum Dokumen
            'analysisResult.document_information.city.string' => 'Kota dokumen harus berupa teks.',
            'analysisResult.document_information.document_date.required' => 'Tanggal dokumen wajib diisi.',
            'analysisResult.document_information.document_date.string' => 'Tanggal dokumen harus berupa teks.',
            'analysisResult.document_information.document_number.string' => 'Nomor dokumen harus berupa teks.',
            'analysisResult.document_information.emitter_email.string' => 'Email pengirim harus berupa teks.',
            'analysisResult.document_information.emitter_organizations.required' => 'Organisasi pengirim wajib diisi.',
            'analysisResult.document_information.emitter_organizations.array' => 'Organisasi pengirim harus berupa daftar.',
            'analysisResult.document_information.emitter_organizations.min' => 'Minimal ada satu organisasi pengirim.',
            'analysisResult.document_information.emitter_organizations.*.name.required' => 'Nama organisasi pengirim wajib diisi.',
            'analysisResult.document_information.emitter_organizations.*.name.string' => 'Nama organisasi pengirim harus berupa teks.',
            'analysisResult.document_information.recipients.required' => 'Penerima wajib diisi.',
            'analysisResult.document_information.recipients.array' => 'Penerima harus berupa daftar.',
            'analysisResult.document_information.recipients.min' => 'Minimal ada satu penerima.',
            'analysisResult.document_information.recipients.*.name.required' => 'Nama penerima wajib diisi.',
            'analysisResult.document_information.recipients.*.name.string' => 'Nama penerima harus berupa teks.',
            'analysisResult.document_information.recipients.*.position.string' => 'Jabatan penerima harus berupa teks.',
            'analysisResult.document_information.subjects.required' => 'Perihal wajib diisi.',
            'analysisResult.document_information.subjects.array' => 'Perihal harus berupa daftar.',
            'analysisResult.document_information.subjects.min' => 'Minimal ada satu perihal.',
            'analysisResult.document_information.subjects.*.required' => 'Setiap perihal wajib diisi.',
            'analysisResult.document_information.subjects.*.string' => 'Perihal harus berupa teks.',

            // Pesan untuk Setiap Kegiatan (Events)
            'analysisResult.events.*.attendees.string' => 'Peserta harus berupa teks.',
            'analysisResult.events.*.date.required' => 'Tanggal kegiatan wajib diisi.',
            'analysisResult.events.*.date.string' => 'Tanggal kegiatan harus berupa teks.',
            'analysisResult.events.*.equipment.array' => 'Daftar peralatan harus berupa daftar.',
            'analysisResult.events.*.equipment.*.item.string' => 'Nama peralatan harus berupa teks.',
            'analysisResult.events.*.equipment.*.quantity.string' => 'Kuantitas peralatan harus berupa teks.', // Catatan: Jika ini seharusnya numerik, ubah di rules() dan pesan ini
            'analysisResult.events.*.eventName.required' => 'Nama kegiatan wajib diisi.',
            'analysisResult.events.*.eventName.string' => 'Nama kegiatan harus berupa teks.',
            'analysisResult.events.*.location.required' => 'Lokasi kegiatan wajib diisi.',
            'analysisResult.events.*.location.string' => 'Lokasi kegiatan harus berupa teks.',
            'analysisResult.events.*.organizers.array' => 'Daftar penanggung jawab harus berupa daftar.',
            'analysisResult.events.*.organizers.*.name.required' => 'Nama penanggung jawab wajib diisi.',
            'analysisResult.events.*.organizers.*.name.string' => 'Nama penanggung jawab harus berupa teks.',
            'analysisResult.events.*.organizers.*.contact.required' => 'Kontak penanggung jawab wajib diisi.',
            'analysisResult.events.*.organizers.*.contact.string' => 'Kontak penanggung jawab harus berupa teks.',
            'analysisResult.events.*.schedule.array' => 'Daftar jadwal harus berupa daftar.',
            'analysisResult.events.*.schedule.*.description.required' => 'Deskripsi jadwal wajib diisi.',
            'analysisResult.events.*.schedule.*.description.string' => 'Deskripsi jadwal harus berupa teks.',
            'analysisResult.events.*.schedule.*.duration.string' => 'Durasi jadwal harus berupa teks.',
            'analysisResult.events.*.schedule.*.startTime.string' => 'Waktu mulai jadwal harus berupa teks.',
            'analysisResult.events.*.schedule.*.endTime.string' => 'Waktu selesai jadwal harus berupa teks.',
            'analysisResult.events.*.time.required' => 'Waktu kegiatan wajib diisi.',
            'analysisResult.events.*.time.string' => 'Waktu kegiatan harus berupa teks.',

            // Pesan untuk Penanda Tangan
            'analysisResult.signature_blocks.required' => 'Penanda tangan wajib diisi.',
            'analysisResult.signature_blocks.array' => 'Penanda tangan harus berupa daftar.',
            'analysisResult.signature_blocks.min' => 'Minimal ada satu penanda tangan.',
            'analysisResult.signature_blocks.*.name.required' => 'Nama penanda tangan wajib diisi.',
            'analysisResult.signature_blocks.*.name.string' => 'Nama penanda tangan harus berupa teks.',
            'analysisResult.signature_blocks.*.position.required' => 'Jabatan penanda tangan wajib diisi.',
            'analysisResult.signature_blocks.*.position.string' => 'Jabatan penanda tangan harus berupa teks.',
        ];
    }

    public function setAnalysisResult(LetterData $analysisResult)
    {
        $this->analysisResult = $analysisResult->toArray();
    }

    public function toDto(): LetterData
    {
        return LetterData::from($this->analysisResult);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function clear(): void
    {
        $this->reset();
    }
}
