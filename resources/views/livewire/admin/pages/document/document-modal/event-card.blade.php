<div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4" x-data="{ currentAiTab: 'detail' }">
    <div class="flex justify-between items-start mb-2">
        <h4 class="font-bold text-md text-slate-800 dark:text-slate-200 pr-2">
            Kegiatan #{{ $index + 1 }}:
            {{ $kegiatan['eventName'] ?? 'Tanpa Nama' }}
        </h4>
        @if ($isEditing)
            <button type="button" wire:click.prevent="removeKegiatan({{ $index }})"
                class="text-red-500 hover:text-red-600 dark:hover:text-red-400 p-1 -mt-1 rounded-full transition-colors flex-shrink-0"
                title="Hapus Kegiatan">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        @endif
    </div>

    {{-- Navigasi Tab untuk setiap kegiatan --}}
    <div class="border-b border-slate-200 dark:border-slate-700">
        <nav class="-mb-px flex space-x-4" aria-label="Tabs">
            <button @click="currentAiTab = 'detail'" type="button"
                :class="currentAiTab === 'detail' ?
                    'border-blue-500 text-blue-600' :
                    'border-transparent text-slate-500 hover:text-slate-700'"
                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Detail</button>
            <button @click="currentAiTab = 'peminjaman'" type="button"
                :class="currentAiTab === 'peminjaman' ?
                    'border-blue-500 text-blue-600' :
                    'border-transparent text-slate-500 hover:text-slate-700'"
                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Peminjaman</button>
        </nav>
    </div>

    {{-- Konten Tab --}}
    <div class="pt-4 text-sm text-slate-700 dark:text-slate-300">
        {{-- Tab Detail --}}
        <div x-show="currentAiTab === 'detail'">
            @if ($isEditing)
                <div class="space-y-4">
                    <div>
                        <x-input-label for="kegiatan-nama-{{ $index }}" value="{{ __('Nama Kegiatan') }}" />
                        <x-text-input id="kegiatan-nama-{{ $index }}" type="text" class="mt-1 block w-full"
                            wire:model.defer="form.analysisResult.events.{{ $index }}.eventName" />
                        <x-input-error :messages="$errors->get('form.analysisResult.events.{{ $index }}.eventName')" class="mt-2" />
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <x-input-label for="tanggal-kegiatan-{{ $index }}"
                                value="{{ __('Tanggal Kegiatan') }}" />

                            {{-- IKON BANTUAN DENGAN POPOVER --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @mouseenter="open = true" @mouseleave="open = false" type="button"
                                    class="text-gray-400 hover:text-gray-600">
                                    {{-- Gunakan ikon SVG tanda tanya --}}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </button>

                                <div x-show="open" x-transition
                                    class="absolute z-10 w-64 p-2 mt-2 -ml-24 text-sm text-white bg-gray-800 rounded-lg shadow-lg">
                                    <h4 class="font-bold">
                                        Contoh format yang didukung:
                                    </h4>
                                    <ul class="mt-1 list-disc list-inside">
                                        <li>7 Juli 2025</li>
                                        <li>
                                            Sabtu - Minggu, 5 - 6 Juli 2025
                                        </li>
                                        <li>
                                            Jumat, 31 Mei dan Minggu, 2 Juni 2024
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <x-text-input id="tanggal-kegiatan-{{ $index }}" type="text"
                            class="mt-1 block w-full"
                            wire:model.defer="form.analysisResult.events.{{ $index }}.date" />
                        <x-input-error :messages="$errors->get('form.analysisResult.events.{{ $index }}.date')" class="mt-2" />
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <x-input-label for="waktu-kegiatan-{{ $index }}"
                                value="{{ __('Waktu Kegiatan') }}" />
                            <div class="relative" x-data="{ open: false }">
                                <button @mouseenter="open = true" @mouseleave="open = false" type="button"
                                    class="text-gray-400 hover:text-gray-600">
                                    {{-- Gunakan ikon SVG tanda tanya --}}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </button>

                                <div x-show="open" x-transition
                                    class="absolute z-10 w-64 p-2 mt-2 -ml-24 text-sm text-white bg-gray-800 rounded-lg shadow-lg">
                                    <h4 class="font-bold">
                                        Contoh format yang didukung:
                                    </h4>
                                    <ul class="mt-1 list-disc list-inside">
                                        <li>19.30 WIB - selesai</li>
                                        <li>09:00 - 15:00 WIB</li>
                                        <li>19.00 WIB</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <x-text-input id="waktu-kegiatan-{{ $index }}" type="text" class="mt-1 block w-full"
                            wire:model.defer="form.analysisResult.events.{{ $index }}.time" />
                        <x-input-error :messages="$errors->get('form.analysisResult.events.{{ $index }}.time')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="lokasi-{{ $index }}" value="{{ __('Lokasi Kegiatan') }}" />
                        <x-text-input id="lokasi-{{ $index }}" type="text" class="mt-1 block w-full"
                            wire:model.defer="form.analysisResult.events.{{ $index }}.location" />
                        <x-input-error :messages="$errors->get('form.analysisResult.events.{{ $index }}.location')" class="mt-2" />
                    </div>
                    <div class="space-y-4">
                        <x-input-label for="organizer-{{ $index }}" value="{{ __('Penanggung Jawab') }}" />
                        @forelse ($form->analysisResult['events'][$index]['organizers'] as $organizerIndex => $organizer)
                            <div wire:key="organizer-{{ $organizerIndex }}"
                                class="p-4 border rounded-lg bg-slate-50 dark:bg-slate-800/50 dark:border-slate-700">
                                <div class="flex items-start justify-between gap-4">
                                    {{-- Bagian Kiri: Form Input --}}
                                    <div class="flex-grow space-y-3">
                                        {{-- Input untuk Nama Penanggung Jawab --}}
                                        <div>
                                            <x-input-label for="organizer_name_{{ $organizerIndex }}"
                                                :value="__('Nama Penanggung Jawab')" />
                                            <x-text-input type="text" id="organizer_name_{{ $organizerIndex }}"
                                                class="w-full mt-1 text-sm"
                                                wire:model.defer="form.analysisResult.events.{{ $index }}.organizers.{{ $organizerIndex }}.name" />
                                            <x-input-error :messages="$errors->get(
                                                'form.analysisResult.events.' .
                                                    $index .
                                                    '.organizers.' .
                                                    $organizerIndex .
                                                    '.name',
                                            )" class="mt-2" />
                                        </div>

                                        {{-- Input untuk Kontak Penanggung Jawab --}}
                                        <div>
                                            <x-input-label for="organizer_contact_{{ $organizerIndex }}"
                                                :value="__('Kontak (No. HP/Email)')" />
                                            <x-text-input type="text" id="organizer_contact_{{ $organizerIndex }}"
                                                class="w-full mt-1 text-sm"
                                                wire:model.defer="form.analysisResult.events.{{ $index }}.organizers.{{ $organizerIndex }}.contact" />
                                            <x-input-error :messages="$errors->get(
                                                'form.analysisResult.events.' .
                                                    $index .
                                                    '.organizers.' .
                                                    $organizerIndex .
                                                    '.contact',
                                            )" class="mt-2" />
                                        </div>
                                    </div>

                                    {{-- Bagian Kanan: Tombol Hapus --}}
                                    <div>
                                        <x-button type="button" variant="danger" size="icon"
                                            title="Hapus Penanggung Jawab"
                                            wire:click="removeOrganizer({{ $index }}, {{ $organizerIndex }})">
                                            {{-- Ikon tempat sampah (SVG) --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 border rounded-lg bg-slate-50 dark:bg-slate-800/50 dark:border-slate-700">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Tidak ada penanggung jawab yang ditambahkan.
                                </p>
                            </div>
                        @endforelse
                        <x-button type="button" variant="primary" size="sm"
                            wire:click="addOrganizers({{ $index }})">
                            + Tambah Penanggung Jawab
                        </x-button>
                    </div>

                    {{-- Schedules --}}
                    <div class="space-y-4">
                        @php
                            $schedule = $form->analysisResult['events'][$index]['schedule'];

                            $showTimeColumn = collect($schedule)->contains(function ($item) {
                                return !empty($item['startTime']) || !empty($item['endTime']);
                            });
                        @endphp
                        <div class="flex items-center space-x-2">
                            <x-input-label for="schedules-{{ $index }}"
                                value="{{ __('Lokasi Kegiatan') }}" />
                            @if ($showTimeColumn)
                                <div class="relative" x-data="{ open: false }">
                                    <button @mouseenter="open = true" @mouseleave="open = false" type="button"
                                        class="text-gray-400 hover:text-gray-600">
                                        {{-- Gunakan ikon SVG tanda tanya --}}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition
                                        class="absolute z-10 w-64 p-2 mt-2 -ml-24 text-sm text-white bg-gray-800 rounded-lg shadow-lg">
                                        <h4 class="font-bold">
                                            Contoh format yang didukung untuk waktu pada agenda:
                                        </h4>
                                        <ul class="mt-1 list-disc list-inside">
                                            <li>17.00</li>
                                            <li>17:00</li>
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="p-4 bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-lg">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-slate-700 dark:text-slate-400">
                                    <thead
                                        class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            @if ($showTimeColumn)
                                                <th class="px-3 py-2">Waktu</th>
                                            @endif
                                            <th class="px-3 py-2">Susunan Acara </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-slate-800">
                                        @forelse ($form->analysisResult['events'][$index]['schedule'] as $scheduleIndex => $item)
                                            <tr wire:key="schedule-{{ $index }}"
                                                class="border-b border-slate-200 dark:border-slate-700">
                                                <td class="p-2 align-top">
                                                    <div class="flex items-center gap-2">
                                                        <x-text-input type="text" class="w-full text-sm"
                                                            wire:model.defer="form.analysisResult.events.{{ $index }}.schedule.{{ $scheduleIndex }}.startTime" />
                                                        <span>-</span>
                                                        <x-text-input type="text" class="w-full text-sm"
                                                            wire:model.defer="form.analysisResult.events.{{ $index }}.schedule.{{ $scheduleIndex }}.endTime" />
                                                    </div>
                                                    <x-input-error :messages="$errors->get(
                                                        'form.analysisResult.events.' . $index . '.startTime',
                                                    )" class="mt-1" />
                                                    <x-input-error :messages="$errors->get(
                                                        'form.analysisResult.events.' . $index . '.endTime',
                                                    )" class="mt-1" />
                                                </td>

                                                {{-- Kolom Deskripsi --}}
                                                <td class="p-2 align-top">
                                                    <x-text-input type="text" class="w-full text-sm"
                                                        placeholder="Deskripsi acara..."
                                                        wire:model.defer="form.analysisResult.events.{{ $index }}.schedule.{{ $scheduleIndex }}.description" />
                                                    <x-input-error :messages="$errors->get(
                                                        'form.analysisResult.events.' . $index . '.description',
                                                    )" class="mt-1" />
                                                </td>

                                                {{-- Kolom Aksi (Tombol Hapus) --}}
                                                <td class="p-2 align-top text-center">
                                                    <x-button type="button" variant="danger" size="icon"
                                                        wire:click="removeScheduleItem({{ $index }},{{ $scheduleIndex }})"
                                                        title="Hapus baris">
                                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </x-button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $showTimeColumn ? 3 : 2 }}"
                                                    class="text-center text-slate-500 dark:text-slate-400 py-4">
                                                    Tidak ada jadwal
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <x-button type="button" variant="primary" size="sm"
                            wire:click="addScheduleItem({{ $index }})">
                            + Tambah Jadwal
                        </x-button>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    {{-- Waktu & Tanggal --}}
                    <div class="flex items-start gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                        <div class="flex-shrink-0 text-blue-500 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ $kegiatan['date'] ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $kegiatan['time'] ?? 'Jam tidak ditentukan' }}
                            </p>
                        </div>
                    </div>
                    {{-- Lokasi --}}
                    <div class="flex items-start gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                        <div class="flex-shrink-0 text-blue-500 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ $kegiatan['location'] ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Lokasi Acara</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                        <div class="flex-shrink-0 text-blue-500 dark:text-blue-400">
                            {{-- MEMPERBAIKI IKON MENJADI IKON PENGGUNA --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ !empty($kegiatan['attendees']) ? $kegiatan['attendees'] : 'N/A' }}
                            </p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Peserta</p>
                        </div>
                    </div>
                    {{-- Penanggung Jawab --}}
                    <div class="space-y-3">
                        @forelse ($kegiatan['organizers'] as $organizer)
                            <div class="flex items-start gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                <div class="flex-shrink-0 text-blue-500 dark:text-blue-400 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $organizer['name'] ?? 'N/A' }}
                                    </p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ $organizer['contact'] ?? 'Kontak tidak tersedia' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-start gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                <div class="flex-shrink-0 text-slate-400 dark:text-slate-500 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Informasi penanggung jawab tidak tersedia.
                                </p>
                            </div>
                        @endforelse
                    </div>
                    {{-- Schedules --}}
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                        @php
                            $schedule = $kegiatan['schedule'];

                            $showTimeColumn = collect($schedule)->contains(function ($item) {
                                return !empty($item['startTime']) || !empty($item['endTime']);
                            });
                        @endphp
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800">
                                <tr>
                                    @if ($showTimeColumn)
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                                            Waktu
                                        </th>
                                    @endif
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                                        Susunan Acara
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                {{-- Ganti $index menjadi $kegiatanIndex juga di sini --}}
                                @forelse ($kegiatan['schedule'] as $schedule)
                                    <tr
                                        class="odd:bg-white dark:odd:bg-slate-800/50 even:bg-slate-50 dark:even:bg-slate-800">
                                        @if ($showTimeColumn)
                                            <td class="px-3 py-2 font-medium whitespace-nowrap">
                                                {{-- Tampilkan waktu jika ada, jika tidak, tampilkan strip --}}
                                                {{ $schedule['startTime'] ?? '' }}
                                                {{ !empty($schedule['startTime']) && !empty($schedule['endTime']) ? '-' : '' }}
                                                {{ $schedule['endTime'] ?? '' }}
                                            </td>
                                        @endif
                                        <td class="px-3 py-2 font-medium">
                                            {{ $schedule['description'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="p-3 text-center text-slate-500"
                                            colspan="{{ $showTimeColumn ? 2 : 1 }}">
                                            Tidak ada susunan acara.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
        {{-- Tab Peminjaman --}}
        <div x-show="currentAiTab === 'peminjaman'">
            @if ($isEditing)
                <div class="space-y-3">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="p-2">Nama Barang</th>
                                <th class="p-2 w-24">Jumlah</th>
                                <th class="p-2 w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            {{-- Ganti $index menjadi $kegiatanIndex untuk kejelasan --}}
                            @forelse ($kegiatan['equipment'] as $itemIndex => $item)
                                <tr wire:key="kegiatan-{{ $index }}-item-{{ $itemIndex }}">
                                    <td class="p-1">
                                        <x-text-input type="text" class="w-full text-sm"
                                            wire:model.defer="form.analysisResult.events.{{ $index }}.equipment.{{ $itemIndex }}.item" />
                                        <x-input-error :messages="$errors->get(
                                            'form.analysisResult.events.{{ $index }}.equipment.{{ $itemIndex }}.item',
                                        )" class="mt-2" />
                                    </td>
                                    <td class="p-1">
                                        <x-text-input type="text" class="w-full text-sm"
                                            wire:model.defer="form.analysisResult.events.{{ $index }}.equipment.{{ $itemIndex }}.quantity" />
                                        <x-input-error :messages="$errors->get(
                                            'form.analysisResult.events.{{ $index }}.equipment.{{ $itemIndex }}.quantity',
                                        )" class="mt-2" />
                                    </td>
                                    <td class="p-1 text-center">
                                        {{-- Tombol untuk menghapus item --}}
                                        <button type="button"
                                            wire:click="removeItem({{ $index }}, {{ $itemIndex }})"
                                            class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-2 rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center p-4 text-slate-500">
                                        Belum ada barang untuk dipinjam.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Tombol untuk menambah item baru --}}
                    <div class="pt-2">
                        <x-button type="button" variant="primary" size="sm"
                            wire:click="addItem({{ $index }})">
                            + Tambah Barang
                        </x-button>
                    </div>
                </div>
            @else
                <div class="overflow-hidden rounded-md border border-slate-200 dark:border-slate-700">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th
                                    class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                                    Barang
                                </th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">
                                    Jumlah
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            {{-- Ganti $index menjadi $kegiatanIndex juga di sini --}}
                            @forelse ($kegiatan['equipment'] as $item)
                                <tr
                                    class="odd:bg-white dark:odd:bg-slate-800/50 even:bg-slate-50 dark:even:bg-slate-800">
                                    <td class="px-3 py-2 font-medium">
                                        {{ $item['item'] }}</td>
                                    <td class="px-3 py-2">
                                        {{ $item['quantity'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-3 text-center text-slate-500" colspan="2">
                                        Tidak ada barang yang dipinjam.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
