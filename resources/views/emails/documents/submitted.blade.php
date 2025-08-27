<x-mail::message>
# Terima Kasih! Dokumen Anda Telah Kami Terima.

Halo **{{ $guest->name }}**,

Terima kasih telah mengajukan dokumen proposal melalui sistem kami. Berikut adalah ringkasan dari pengajuan Anda.

- **Nama Dokumen:** {{ $document->original_file_name }}
- **Tanggal Diajukan:** {{ $document->created_at->translatedFormat('l, d F Y - H:i') }} WIB

Dokumen Anda akan segera kami proses. Anda akan menerima notifikasi selanjutnya jika diperlukan.

Jika Anda memiliki pertanyaan, silakan balas email ini.

Hormat kami,<br>
Tim {{ config('app.name') }}
</x-mail::message>
