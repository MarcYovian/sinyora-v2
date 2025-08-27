<x-mail::message>
# Terima Kasih! Proposal Anda Telah Kami Terima.

Halo **{{ $guest->name }}**,

Terima kasih telah mengajukan proposal kegiatan melalui sistem kami. Ini adalah ringkasan dari pengajuan yang Anda kirimkan.

**Nomor Referensi Proposal:** `EVT-{{ str_pad($event->id, 6, '0', STR_PAD_LEFT) }}`

---

### **1. Detail Kegiatan Anda**
- **Nama Kegiatan:** {{ $event->name }}
- **Penyelenggara:** {{ $event->organization->name ?? 'N/A' }}
- **Deskripsi:**
<x-mail::panel>
{{ $event->description }}
</x-mail::panel>

---

### **2. Jadwal & Lokasi**
@php
    $recurrence = $event->firstRecurrence;
    $startDateTime = \Carbon\Carbon::parse($recurrence->date->format('Y-m-d') . ' ' . $recurrence->time_start->format('H:i:s'));
    $endDateTime = $event->getContinuousEndDate();
@endphp
- **Waktu Mulai:** {{ $startDateTime->translatedFormat('l, d F Y - H:i') }} WIB
- **Waktu Selesai:** {{ $endDateTime->translatedFormat('l, d F Y - H:i') }} WIB
- **Lokasi yang Diajukan:**
    <ul>
    @foreach($event->locations as $location)
        <li>{{ $location->name }}</li>
    @endforeach
    </ul>

---

### **3. Ringkasan Peminjaman Aset**
@php
    $borrowing = $event->borrowings->first();
@endphp

@if ($borrowing && $borrowing->assets->isNotEmpty())
- **Aset yang Diajukan:**
    <ul>
    @foreach($borrowing->assets as $asset)
        <li>{{ $asset->name }} (Jumlah: {{ $asset->pivot->quantity }})</li>
    @endforeach
    </ul>
- **Catatan Tambahan Anda:**
<x-mail::panel>
{{ $borrowing->notes ?? 'Tidak ada catatan tambahan.' }}
</x-mail::panel>
@else
<p>Anda tidak mengajukan peminjaman aset untuk kegiatan ini.</p>
@endif

---

Proposal Anda akan segera kami tinjau. Anda akan menerima notifikasi selanjutnya melalui email setelah proposal disetujui atau ditolak.

Jika Anda memiliki pertanyaan, silakan balas email ini.

Hormat kami,<br>
Tim {{ config('app.name') }}
</x-mail::message>
