<x-mail::message>
# Dokumen Proposal Baru: "{{ $document->original_file_name }}"

Ada dokumen proposal baru yang diajukan oleh **{{ $guest->name }}** dan membutuhkan tinjauan Anda.

---

### **Detail Pengaju**
- **Nama:** {{ $guest->name }}
- **Email:** <a href="mailto:{{ $guest->email }}">{{ $guest->email }}</a>
- **No. Telepon (WA):** {{ $guest->phone_number }}

---

### **Detail Dokumen**
- **Nama File Asli:** {{ $document->original_file_name }}
- **Waktu Unggah:** {{ $document->created_at->translatedFormat('l, d F Y - H:i') }} WIB

Silakan masuk ke dasbor admin untuk meninjau dan memproses dokumen proposal ini.

<x-mail::button :url="url('/admin/documents')"> {{-- Sesuaikan URL jika perlu --}}
    Lihat Dokumen
</x-mail::button>

Terima kasih,<br>
Sistem Notifikasi {{ config('app.name') }}
</x-mail::message>
