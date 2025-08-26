<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Livewire\Forms\DocumentForm;
use App\Models\Document;
use App\Services\DocumentFinalizationService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class EventModal extends Component
{
    public ?DocumentForm $form;
    public array $data = [];

    #[On('setDataForEvent')]
    public function setData(array $data)
    {
        $this->data = $data;

        foreach ($this->data['events'] as $index => &$event) { // Gunakan referensi (&) untuk memodifikasi langsung

            $dateRanges = Arr::get($event, 'parsed_dates.dates', []);
            if (empty($dateRanges)) {
                continue; // Lanjut ke event berikutnya jika tidak ada data tanggal
            }

            $formattedDateStrings = [];
            $formattedTime = '';

            foreach ($dateRanges as $i => $range) {
                $startString = Arr::get($range, 'start');
                $endString = Arr::get($range, 'end');

                // Lewati jika data start atau end tidak valid
                if (!$startString || !$endString) {
                    continue;
                }

                try {
                    $startDate = Carbon::parse($startString);
                    $endDate = Carbon::parse($endString);

                    // --- Ambil Waktu dari Rentang Pertama sebagai Waktu Utama ---
                    if ($i === 0) {
                        $formattedTime = $startDate->isoFormat('HH:mm') . ' - ' . $endDate->isoFormat('HH:mm') . ' WIB';
                    }

                    // --- Format String Tanggal untuk Setiap Rentang ---
                    if ($startDate->isSameDay($endDate)) {
                        $formattedDateStrings[] = $startDate->isoFormat('dddd, D MMMM YYYY');
                    } else {
                        // Jika tahunnya sama, jangan ulangi tahunnya
                        if ($startDate->isSameYear($endDate)) {
                            $formattedDateStrings[] = $startDate->isoFormat('dddd, D MMMM') . ' - ' . $endDate->isoFormat('dddd, D MMMM YYYY');
                        } else {
                            $formattedDateStrings[] = $startDate->isoFormat('dddd, D MMMM YYYY') . ' - ' . $endDate->isoFormat('dddd, D MMMM YYYY');
                        }
                    }
                } catch (\Exception $e) {
                    // Tangani jika format tanggal tidak valid, misalnya dengan logging
                    continue;
                }
            }

            // --- Gabungkan Semua String Tanggal Menjadi Satu ---
            $event['formatted_date'] = implode(' dan ', $formattedDateStrings);
            $event['formatted_time'] = $formattedTime;
        }
        unset($event); // Hapus referensi setelah loop selesai
        // dd($this->data); // Debugging line, can be removed later
    }

    public function save()
    {
        app(DocumentFinalizationService::class)->finalize($this->data);
        $this->dispatch('refresh-documents');
        toastr()->success(__('Data Dokumen berhasil diproses dan disimpan.'));
        $this->dispatch('close-modal', 'event-modal');
    }

    public function render()
    {
        return view('livewire.admin.pages.document.event-modal');
    }
}
