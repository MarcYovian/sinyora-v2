<?php

namespace App\Listeners;

use App\Events\EventProposalCreated;
use App\Mail\EventProposalSubmitted;
use App\Mail\NewEventProposalAdmin;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventProposalNotifications implements ShouldQueue
{
    public $queue = 'notifications';
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventProposalCreated $event): void
    {
        try {
            Mail::to($event->guest->email)->queue(new EventProposalSubmitted($event->guest, $event->event));

            $userRepository = app(EloquentUserRepository::class);
            $adminAndManagers = $userRepository->getAdminsAndManagers();
            if ($adminAndManagers->isNotEmpty()) {
                Mail::to($adminAndManagers)->queue(new NewEventProposalAdmin($event->guest, $event->event));
            }
        } catch (\Exception $e) {
            Log::error('Gagal menjalankan job notifikasi proposal: ' . $e->getMessage(), [
                'event_id' => $event->event->id,
                'guest_email' => $event->guest->email,
            ]);
        }
    }
}
