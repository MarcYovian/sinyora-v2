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

    public function storeDocument()
    {
        $data = $this->data;

        $organizationId = $data['document_information']['final_organization_id'] ?? collect($data['document_information']['emitter_organizations'])->whereNotNull('nama_organisasi_id')->pluck('nama_organisasi_id')->first();
        $type = $data['type'];
        $userId = Auth::id();

        DB::transaction(function () use ($data, $type, $organizationId, $userId) {
            $document = Document::findOrFail($data['id']);
            $document->update([
                'email' => data_get($data, 'document_information.emitter_email'),
                'subject' => implode(', ', data_get($data, 'document_information.subjects', [])),
                'city' => data_get($data, 'document_information.document_city'),
                'doc_date' => data_get($data, 'document_information.document_date.date'),
                'doc_num' => data_get($data, 'document_information.document_number'),
                'status' => 'done',
                'processed_by' => $userId,
                'processed_at' => now(),
            ]);

            foreach (data_get($data, 'signature_blocks', []) as $signatureData) {
                $document->signatures()->create([
                    'name' => $signatureData['name'],
                    'position' => $signatureData['position'],
                ]);
            }

            if ($type === 'perizinan') {
                foreach ($data['events'] as $eventData) {
                    $this->storeEvent($eventData, $organizationId, $document, $type);
                }
            } else if ($type === 'peminjaman') {
                foreach ($data['events'] as $eventData) {
                    $this->storeBorrowing($eventData, $document);
                }
            } else if ($type === 'undangan') {
                foreach ($data['events'] as $eventData) {
                    $this->storeInvitation($eventData, $document, $data['document_information']);
                }
            }
        });
    }

    private function storeEvent(array $dataEvent, int $organizationId, Document $document, string $type)
    {
        $admin = ModelsUser::find(Auth::id());
        $matchedLocations = collect($dataEvent['location_data'])
            ->where('match_status', 'matched')
            ->whereNotNull('location_id');

        $docRelationId = [];

        foreach ($dataEvent['dates'] as $datePair) {
            $startDateTime = Carbon::parse($datePair['start']);
            $endDateTime = Carbon::parse($datePair['end']);

            // 1. Buat satu record Event untuk setiap jadwal
            $event = new Event([
                'name' => $dataEvent['eventName'],
                'start_recurring' => $startDateTime->format('Y-m-d'),
                'end_recurring' => $endDateTime->format('Y-m-d'),
                'status' => EventApprovalStatus::PENDING,
                'recurrence_type' => EventRecurrenceType::DAILY, // atau tipe lain sesuai kebutuhan
                'organization_id' => $organizationId,
                'event_category_id' => $dataEvent['fivetask_categories']['id'],
            ]);
            $admin->events()->save($event);

            // Hubungkan lokasi ke event yang baru dibuat
            if ($matchedLocations->isNotEmpty()) {
                $locationIds = $matchedLocations->pluck('location_id')->all();
                $event->locations()->sync($locationIds);
            } elseif (!empty($dataEvent['location'])) {
                $location = CustomLocation::firstOrCreate([
                    'name' => $dataEvent['location'],
                ]);

                $event->customLocations()->sync([$location->id]);
            }

            if ($type === 'perizinan') {
                $licensing = LicensingDocument::create([
                    'description' => $dataEvent['eventName'],
                    'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                ]);

                $licensing->events()->save($event);

                $docRelationId[] = $licensing->id;
            }

            // 2. Buat record peminjaman jika ada barang
            if (!empty($dataEvent['equipment'])) {
                $borrowing = new Borrowing([
                    'event_id' => $event->id,
                    'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                    'borrower' => data_get($dataEvent, 'organizers.0.name', 'N/A'),
                    'borrower_phone' => data_get($dataEvent, 'organizers.0.contact', 'N/A'),
                    'status' => BorrowingStatus::PENDING
                ]);

                $admin->borrowings()->save($borrowing);

                $assetIds = collect($dataEvent['equipment'])->map(function ($asset) {
                    return [
                        'asset_id' => $asset['item_id'],
                        'quantity' => $asset['quantity'],
                    ];
                });

                $borrowing->assets()->sync($assetIds);
                $licensing->borrowings()->save($borrowing);
            }

            // 4. Handle pembuatan jadwal perulangan (recurrence)
            $this->handleRecurrence($event, $startDateTime, $endDateTime, $locationIds);
        }

        $document->licensingDocuments()->attach($docRelationId);
    }

    private function storeBorrowing(array $dataEvent, Document $document)
    {
        $admin = ModelsUser::find(Auth::id());
        $docRelationId = [];

        foreach ($dataEvent['dates'] as $datePair) {
            $startDateTime = Carbon::parse($datePair['start']);
            $endDateTime = Carbon::parse($datePair['end']);

            // 3. Buat record peminjaman
            $borrowingDocument = BorrowingDocument::create([
                'start_borrowing' => $startDateTime->format('Y-m-d H:i:s'),
                'end_borrowing' => $endDateTime->format('Y-m-d H:i:s'),
            ]);

            $docRelationId[] = $borrowingDocument->id;

            // 2. Buat record peminjaman jika ada barang
            if (!empty($dataEvent['equipment'])) {
                $borrowing = new Borrowing([
                    'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                    'borrower' => $dataEvent['organizers'][0]['name'] ?? 'N/A',
                    'borrower_phone' => $dataEvent['organizers'][0]['contact'] ?? 'N/A',
                    'status' => BorrowingStatus::PENDING
                ]);

                $admin->borrowings()->save($borrowing);

                $assetIds = collect($dataEvent['equipment'])->map(function ($asset) {
                    return [
                        'asset_id' => $asset['item_id'],
                        'quantity' => $asset['quantity'],
                    ];
                });
                $borrowing->assets()->sync($assetIds);

                $borrowingDocument->borrowings()->save($borrowing);
            }
        }

        $document->licensingDocuments()->attach($docRelationId);
    }

    private function storeInvitation(array $dataEvent, Document $document, array $information)
    {
        $docRelationId = [];

        foreach ($dataEvent['dates'] as $datePair) {
            $startDateTime = Carbon::parse($datePair['start']);
            $endDateTime = Carbon::parse($datePair['end']);

            // Buat record undangna
            $invitation = InvitationDocument::create([
                'event' => $dataEvent['eventName'] ?? 'N/A',
                'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                'location' => $dataEvent['location'] ?? 'N/A',
            ]);

            foreach ($information['recipients'] as $recipient) {
                $invitation->recipients()->create([
                    'recipient' => $recipient['name'],
                    'recipient_position' => $recipient['position'] ?? null,
                ]);
            }

            foreach ($dataEvent['schedule'] as $schedule) {
                $duration = null;
                $scheduleData = [
                    'description' => $schedule['description']
                ];
                if (!empty($schedule['startTime_processed']['time']) && !empty($schedule['endTime_processed']['time'])) {
                    try {
                        $startTime = Carbon::parse($schedule['startTime_processed']['time']);
                        $endTime = Carbon::parse($schedule['endTime_processed']['time']);
                        $duration = $startTime->diffInMinutes($endTime) . "'";
                        $scheduleData['start_time'] = $startTime->format('H:i:s');
                        $scheduleData['end_time'] = $endTime->format('H:i:s');
                        $schedule['duration'] = $duration;
                    } catch (Exception $e) {
                        // Jika format waktu salah, durasi akan tetap null.
                        // Ini adalah fallback yang aman.
                        Log::warning('Gagal menghitung durasi jadwal: ' . $e->getMessage());
                    }
                }
                $invitation->schedules()->create($scheduleData);
            }

            $docRelationId[] = $invitation->id;
        }

        $document->invitationDocuments()->attach($docRelationId);
    }

    protected function handleRecurrence(Event $event, Carbon $startDateTime, Carbon $endDateTime, array $locationIds): void
    {
        $startTime = $startDateTime->format('H:i:s');
        $endTime = $endDateTime->format('H:i:s');

        $this->generateSingleRecurrence($event, $startDateTime, $endDateTime, $startTime, $endTime, $locationIds);
    }

    protected function generateSingleRecurrence(Event $event, Carbon $startDate, Carbon $endDate, string $startTime, string $endTime, array $locationIds): void
    {
        $isSameDay = $startDate->isSameDay($endDate);

        $this->generateDailyRecurrences(
            $event->id,
            $startDate,
            $startTime,
            !$isSameDay ? '23:59:59' : $endTime,
            $locationIds
        );

        if (!$isSameDay) {
            $currentDate = $startDate->copy()->addDay();

            while ($currentDate->lte($endDate)) {
                $timeStart = '00:00:00';
                $timeEnd = $currentDate->isSameDay($endDate) ? $endTime : '23:59:59';

                $this->generateDailyRecurrences(
                    $event->id,
                    $currentDate->format('Y-m-d'),
                    $timeStart,
                    $timeEnd,
                    $locationIds
                );

                $currentDate->addDay();
            }
        }
    }

    protected function generateDailyRecurrences(
        int $eventId,
        string $date,
        string $timeStart,
        string $timeEnd,
        array $locations
    ): void {
        $this->validateNoConflict($date, $timeStart, $timeEnd, $locations);

        EventRecurrence::create([
            'event_id' => $eventId,
            'date' => $date,
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
        ]);
    }

    protected function validateNoConflict(
        string $date,
        string $startTime,
        string $endTime,
        array $locations
    ): void {

        $conflictExists = EventRecurrence::whereHas('event.locations', function ($q) use ($locations) {
            $q->whereIn('locations.id', $locations);
        })->whereHas('event', function ($q) {
            $q->where('status', EventApprovalStatus::APPROVED);
        })
            ->where('date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where([
                    ['time_start', '<', $endTime],
                    ['time_end', '>', $startTime]
                ]);
            })
            ->select('id')
            ->exists();
        // dd($conflictExists, $startTime, $endTime, $date);
        if ($conflictExists) {
            Log::warning('There is a conflict', [
                'date' => $date,
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);
            throw ValidationException::withMessages([
                'conflict' => __('The schedule conflicts with an existing event on :date between :start and :end', [
                    'date' => Carbon::parse($date)->format('M j, Y'),
                    'start' => Carbon::parse($startTime)->format('g:i A'),
                    'end' => Carbon::parse($endTime)->format('g:i A')
                ])
            ]);
        }
    }

    public function clear(): void
    {
        $this->reset();
    }
}
