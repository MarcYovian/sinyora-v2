<x-mail::message>
# Pesan Kontak Baru

Ada pesan baru dari pengunjung website:

<x-mail::panel>
**Nama:** {{ $contact->name }}

**Email:** {{ $contact->email }}

**Telepon:** {{ $contact->phone ?? '-' }}

**Tanggal:** {{ $contact->created_at->format('d M Y, H:i') }}
</x-mail::panel>

## Pesan:

{{ $contact->message }}

<x-mail::button :url="config('app.url') . '/admin/contacts'">
Lihat di Admin Panel
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
