<?php

namespace App\Listeners;

use App\Events\EventApproved;
use App\Events\EventRejected;
use App\Mail\EventStatusUpdatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventStatusEventSubscriber
{
    public function handleEventStatusApproved(EventApproved $event)
    {
        try {
            Mail::to($event->user->email)->queue(new EventStatusUpdatedMail($event->event));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email notifikasi kegiatan: ' . $e->getMessage(), [
                'event_id' => $event->event->id,
                'user_email' => $event->user->email,
            ]);
            // Melempar kembali exception agar job bisa di-retry jika perlu
            throw $e;
        }
    }

    public function handleEventStatusRejected(EventRejected $event)
    {
        try {
            Mail::to($event->user->email)->queue(new EventStatusUpdatedMail($event->event, $event->reason));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email notifikasi kegiatan: ' . $e->getMessage(), [
                'event_id' => $event->event->id,
                'user_email' => $event->user->email,
            ]);
            // Melempar kembali exception agar job bisa di-retry jika perlu
            throw $e;
        }
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            EventApproved::class,
            [EventStatusEventSubscriber::class, 'handleEventStatusApproved']
        );

        $events->listen(
            EventRejected::class,
            [EventStatusEventSubscriber::class, 'handleEventStatusRejected']
        );
    }
}
