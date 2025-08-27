<?php

namespace App\Listeners;

use App\Events\DocumentProposalCreated;
use App\Mail\DocumentProposalSubmitted;
use App\Mail\NewDocumentProposalAdmin;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDocumentProposalNotifications implements ShouldQueue
{
    /**
     * Nama koneksi antrian.
     *
     * @var string|null
     */
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
    public function handle(DocumentProposalCreated $event): void
    {
        try {
            // 1. Kirim email konfirmasi ke pengguna
            Mail::to($event->guest->email)
                ->queue(new DocumentProposalSubmitted($event->guest, $event->document));

            // 2. Kirim email notifikasi ke admin
            // Asumsi: Anda punya role 'admin' dan 'manager'
            $userRepository = app(EloquentUserRepository::class);
            $adminsAndManagers = $userRepository->getAdminsAndManagers();

            if ($adminsAndManagers->isNotEmpty()) {
                Mail::to($adminsAndManagers)
                    ->queue(new NewDocumentProposalAdmin($event->guest, $event->document));
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email notifikasi dokumen: ' . $e->getMessage(), [
                'document_id' => $event->document->id,
                'guest_email' => $event->guest->email,
            ]);
            // Melempar kembali exception agar job bisa di-retry jika perlu
            throw $e;
        }
    }
}
