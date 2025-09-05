<x-mail::message>
# Informasi Mengenai Proposal Kegiatan Anda

Halo **{{ $guest->name }}**,

Terima kasih telah mengajukan proposal kegiatan **"{{ $event->name }}"**. Setelah melakukan peninjauan, dengan berat hati kami sampaikan bahwa proposal Anda saat ini belum dapat kami setujui.

@if($reason)
**Alasan Penolakan:**
<x-mail::panel>
{{ $reason }}
</x-mail::panel>
@endif

Kami sangat menghargai inisiatif Anda. Jangan ragu untuk mengajukan proposal kembali di lain kesempatan atau menghubungi kami jika ada yang ingin didiskusikan.

Terima kasih atas pengertian Anda.

Hormat kami,<br>
Tim {{ config('app.name') }}
</x-mail::message>
