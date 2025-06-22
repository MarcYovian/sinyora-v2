<?php

namespace App\Livewire\Forms;

use App\Enums\BorrowingStatus;
use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
use App\Models\Borrowing;
use App\Models\BorrowingDocument;
use App\Models\Document;
use App\Models\Event;
use App\Models\EventRecurrence;
use App\Models\InvitationDocument;
use App\Models\LicensingDocument;
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

    public function setData($data)
    {
        $this->data = $data;
    }

    public function storeDocument()
    {
        $data = $this->data;
        $organizationId = collect($data['informasi_umum_dokumen']['organisasi'])->whereNotNull('nama_organisasi_id')->pluck('nama_organisasi_id')->first();
        $type = $data['type'];
        // dd($data);
        // dd(Document::findOrFail($data['id']));

        DB::transaction(function () use ($data, $type, $organizationId) {
            $document = Document::findOrFail($data['id']);
            $date = $this->getDate($data['informasi_umum_dokumen']['tanggal_surat_dokumen']);
            $document->update([
                'email' => $data['informasi_umum_dokumen']['email_pengirim'],
                'subject' => $data['informasi_umum_dokumen']['perihal_surat'],
                'city' => $data['informasi_umum_dokumen']['kota_surat'],
                'doc_date' => $date,
                'doc_num' => $data['informasi_umum_dokumen']['nomor_surat'],
                'status' => 'done',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            if ($type === 'perizinan') {
                foreach ($data['detail_kegiatan'] as $eventData) {
                    $this->storeEvent($eventData, $organizationId, $document, $type);
                }
            } else if ($type === 'peminjaman') {
                foreach ($data['detail_kegiatan'] as $eventData) {
                    $this->storeBorrowing($eventData, $document);
                }
            } else if ($type === 'undangan') {
                foreach ($data['detail_kegiatan'] as $eventData) {
                    $this->storeInvitation($eventData, $document, $data['informasi_umum_dokumen']);
                }
            }
        });
    }

    private function storeEvent(array $dataEvent, int $organizationId, Document $document, string $type)
    {
        $admin = Auth::user();
        $locationIds = collect($dataEvent['location_data'])->pluck('location_id')->all();

        $docRelationId = [];

        foreach ($dataEvent['dates'] as $datePair) {
            $startDateTime = Carbon::parse($datePair['start']);
            $endDateTime = Carbon::parse($datePair['end']);

            // 1. Buat satu record Event untuk setiap jadwal
            $event = new Event([
                'name' => $dataEvent['nama_kegiatan_utama'],
                'description' => $dataEvent['catatan_tambahan'],
                'start_recurring' => $startDateTime->format('Y-m-d'),
                'end_recurring' => $endDateTime->format('Y-m-d'),
                'status' => EventApprovalStatus::PENDING,
                'recurrence_type' => EventRecurrenceType::DAILY, // atau tipe lain sesuai kebutuhan
                'organization_id' => $organizationId,
                'event_category_id' => $dataEvent['kategori_pancatugas']['id'],
            ]);
            $admin->events()->save($event);

            // Hubungkan lokasi ke event yang baru dibuat
            $event->locations()->sync($locationIds);

            // 2. Buat record peminjaman jika ada barang
            if (!empty($dataEvent['barang_dipinjam'])) {
                $borrowing = Borrowing::create([
                    'created_by' => Auth::id(),
                    'event_id' => $event->id,
                    'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                    'borrower' => $dataEvent['penanggung_jawab'],
                    'borrower_phone' => $dataEvent['kontak_pj'],
                    'status' => BorrowingStatus::PENDING
                ]);
                $assetIds = collect($dataEvent['barang_dipinjam'])->map(function ($asset) {
                    return [
                        'asset_id' => $asset['item_id'],
                        'quantity' => $asset['jumlah'],
                    ];
                });
                $borrowing->assets()->sync($assetIds);
            }

            // 3. Buat record perizinan
            if ($type === 'perizinan') {
                $licensing = LicensingDocument::create([
                    'description' => $dataEvent['deskripsi_tambahan_kegiatan'],
                    'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                ]);

                $docRelationId[] = $licensing->id;
            }

            // 4. Handle pembuatan jadwal perulangan (recurrence)
            $this->handleRecurrence($event, $startDateTime, $endDateTime, $locationIds);
        }

        $document->licensingDocuments()->attach($docRelationId);
    }

    private function storeBorrowing(array $dataEvent, Document $document)
    {
        $docRelationId = [];

        foreach ($dataEvent['dates'] as $datePair) {
            $startDateTime = Carbon::parse($datePair['start']);
            $endDateTime = Carbon::parse($datePair['end']);

            // 2. Buat record peminjaman jika ada barang
            if (!empty($dataEvent['barang_dipinjam'])) {
                $borrowing = Borrowing::create([
                    'created_by' => Auth::id(),
                    'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                    'borrower' => $dataEvent['penanggung_jawab'],
                    'borrower_phone' => $dataEvent['kontak_pj'],
                    'status' => BorrowingStatus::PENDING
                ]);
                $assetIds = collect($dataEvent['barang_dipinjam'])->map(function ($asset) {
                    return [
                        'asset_id' => $asset['item_id'],
                        'quantity' => $asset['jumlah'],
                    ];
                });
                $borrowing->assets()->sync($assetIds);
            }

            // 3. Buat record peminjaman
            $borrowing = BorrowingDocument::create([
                'description' => $dataEvent['deskripsi_tambahan_kegiatan'],
                'start_borrowing' => $startDateTime->format('Y-m-d H:i:s'),
                'end_borrowing' => $endDateTime->format('Y-m-d H:i:s'),
            ]);

            $docRelationId[] = $borrowing->id;
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
                'event' => $dataEvent['nama_kegiatan_utama'],
                'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                'location' => $dataEvent['lokasi_kegiatan'],
                'description' => $dataEvent['deskripsi_tambahan_kegiatan']
            ]);

            foreach ($information['penerima_surat'] as $recipient) {
                $invitation->recipients()->create([
                    'recipient' => $recipient['name'],
                    'recipient_position' => $recipient['position'],
                ]);
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

    private function getDate($date)
    {
        if (empty($date)) return null;

        try {
            Carbon::setLocale('id_ID');
            $indonesianMonths = [
                'januari' => 'january',
                'februari' => 'february',
                'maret' => 'march',
                'april' => 'april',
                'mei' => 'may',
                'juni' => 'june',
                'juli' => 'july',
                'agustus' => 'august',
                'september' => 'september',
                'oktober' => 'october',
                'november' => 'november',
                'desember' => 'december'
            ];
            $date = str_ireplace(array_keys($indonesianMonths), array_values($indonesianMonths), $date);
            return Carbon::parse($date)->format('Y-m-d');
        } catch (Exception $e) {
            Log::error("Gagal mem-parsing tanggal '{$date}'. Error: " . $e->getMessage());
            return 'Format tidak valid';
        }
    }
}
