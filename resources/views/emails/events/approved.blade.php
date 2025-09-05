<x-mail::message>
# Proposal Kegiatan Anda Disetujui!

Halo **{{ $guest->name }}**,

Kabar baik! Proposal kegiatan Anda untuk **"{{ $event->name }}"** telah disetujui.

Berikut adalah ringkasan detail kegiatan yang telah disetujui:

- **Nama Kegiatan:** {{ $event->name }}
- **Jadwal:** {{ $event->firstRecurrence->date->translatedFormat('l, d F Y') }}, pukul {{ $event->firstRecurrence->time_start->format('H:i') }} WIB

Silakan hubungi kami jika Anda memiliki pertanyaan lebih lanjut mengenai persiapan kegiatan.

Terima kasih atas kontribusi Anda.

Hormat kami,<br>
Tim {{ config('app.name') }}
</x-mail::message>
