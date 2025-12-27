<?php

namespace App\Listeners;

use App\Events\ContactSubmitted;
use App\Mail\NewContactSubmittedMail;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendContactNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'notifications';

    /**
     * Jumlah percobaan maksimal
     */
    public $tries = 3;

    /**
     * Waktu tunggu antar retry (detik)
     */
    public $backoff = [10, 30, 60];

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
    public function handle(ContactSubmitted $event): void
    {
        try {
            $userRepository = app(EloquentUserRepository::class);
            $adminAndManagers = $userRepository->getAdminsAndManagers();

            if ($adminAndManagers->isNotEmpty()) {
                Mail::to($adminAndManagers)->queue(new NewContactSubmittedMail($event->contact));
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi pesan kontak: ' . $e->getMessage(), [
                'contact_id' => $event->contact->id,
                'contact_email' => $event->contact->email,
            ]);

            // Lempar exception untuk retry
            throw $e;
        }
    }
}
