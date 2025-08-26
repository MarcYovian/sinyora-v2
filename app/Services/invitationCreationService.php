<?php

namespace App\Services;

use App\Models\Document;
use App\Models\InvitationDocument;
use App\Repositories\Contracts\InvitationDocumentRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class invitationCreationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected InvitationDocumentRepositoryInterface $invitationDocumentRepository
    ) {}

    public function createInvitationFromDocument(Document $document, array $data, array $information)
    {
        foreach (data_get($data, 'parsed_dates.dates', []) as $date) {
            $startDateTime = Carbon::parse($date['start']);
            $endDateTime = Carbon::parse($date['end']);

            $invitation = $this->invitationDocumentRepository->create([
                'event' => $data['eventName'] ?? 'N/A',
                'start_datetime' => $startDateTime,
                'end_datetime' => $endDateTime,
                'location' => $data['location'] ?? 'N/A',
            ]);

            $document->invitationDocuments()->attach($invitation->id);

            foreach ($information['recipients'] as $recipient) {
                $invitation->recipients()->create([
                    'recipient' => $recipient['name'],
                    'recipient_position' => $recipient['position'] ?? null,
                ]);
            }

            foreach ($data['schedule'] as $schedule) {
                $this->createScheduleForInvitation($invitation, $schedule);
            }
        }
    }

    private function createScheduleForInvitation(InvitationDocument $invitation, array $schedule): void
    {
        $scheduleData = ['description' => $schedule['description']];

        if (!empty($schedule['parsed_start_time']['time']) && !empty($schedule['parsed_end_time']['time'])) {
            try {
                $startTime = Carbon::parse($schedule['parsed_start_time']['time']);
                $endTime = Carbon::parse($schedule['parsed_end_time']['time']);

                $scheduleData['start_time'] = $startTime->format('H:i:s');
                $scheduleData['end_time'] = $endTime->format('H:i:s');
                $scheduleData['duration'] = $startTime->diffInMinutes($endTime) . "'";
            } catch (Exception $e) {
                Log::warning('Gagal mem-parsing waktu jadwal undangan: ' . $e->getMessage());
            }
        }
        $invitation->schedules()->create($scheduleData);
    }
}
