<?php

namespace App\Livewire\Forms;

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
                $borrowing = Borrowing::create([
                    'created_by' => Auth::id(),
                    'event_id' => $event->id,
                    'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                    'borrower' => data_get($dataEvent, 'organizers.0.name', 'N/A'),
                    'borrower_phone' => data_get($dataEvent, 'organizers.0.contact', 'N/A'),
                    'status' => BorrowingStatus::PENDING
                ]);
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
                $borrowing = Borrowing::create([
                    'created_by' => Auth::id(),
                    'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $endDateTime->format('Y-m-d H:i:s'),
                    'borrower' => $dataEvent['organizers'][0]['name'] ?? 'N/A',
                    'borrower_phone' => $dataEvent['organizers'][0]['contact'] ?? 'N/A',
                    'status' => BorrowingStatus::PENDING
                ]);
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
}
