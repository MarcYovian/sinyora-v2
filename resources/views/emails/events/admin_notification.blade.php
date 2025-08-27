<x-mail::message>
# Proposal Kegiatan Baru: "{{ $event->name }}"

Ada proposal kegiatan baru yang diajukan oleh **{{ $guest->name }}** dan membutuhkan tinjauan Anda.

---

### **1. Detail Pengaju**
- **Nama:** {{ $guest->name }}
- **Email:** <a href="mailto:{{ $guest->email }}">{{ $guest->email }}</a>
- **No. Telepon (WA):** <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $guest->phone_number) }}">{{ $guest->phone_number }}</a>

---

### **2. Detail Kegiatan**
- **Nama Kegiatan:** {{ $event->name }}
- **Penyelenggara:** {{ $event->organization->name ?? 'N/A' }}
- **Deskripsi:**
<x-mail::panel>
{{ $event->description }}
</x-mail::panel>

---

### **3. Jadwal & Lokasi**
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

### **4. Kebutuhan Peminjaman Aset**
@php
    $borrowing = $event->borrowings->first();
@endphp

@if ($borrowing && $borrowing->assets->isNotEmpty())
- **Aset yang Dipinjam:**
    <ul>
    @foreach($borrowing->assets as $asset)
        <li>{{ $asset->name }} (Jumlah: {{ $asset->pivot->quantity }})</li>
    @endforeach
    </ul>
- **Catatan Peminjaman:**
<x-mail::panel>
{{ $borrowing->notes ?? 'Tidak ada catatan tambahan.' }}
</x-mail::panel>
@else
<p>Tidak ada pengajuan peminjaman aset untuk kegiatan ini.</p>
@endif

---

Silakan masuk ke dasbor admin untuk meninjau, menyetujui, atau menolak proposal ini.

<x-mail::button :url="route('admin.events.index')">
    Tinjau Proposal Sekarang
</x-mail::button>

Terima kasih,<br>
Sistem Notifikasi {{ config('app.name') }}
</x-mail::message>
