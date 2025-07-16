<div>
    <x-modal name="invitation-document-detail-modal" maxWidth="5xl" focusable> {{-- Adjust maxWidth as needed --}}
        @if (!empty($documentData)) {{-- Check if documentData is available --}}
            <div class="p-6 bg-white dark:bg-gray-800">

                {{-- Modal Header --}}
                <div class="flex justify-between items-start pb-4 border-b border-gray-200 dark:border-gray-700 mb-6">
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">
                            Detail Dokumen Undangan
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Nomor Dokumen: <span
                                class="font-bold text-gray-800 dark:text-gray-200">{{ $documentData['documents'][0]['doc_num'] ?? '-' }}</span>
                        </p>
                    </div>
                    <button x-on:click="$dispatch('close-modal', 'invitation-document-detail-modal')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition duration-150 ease-in-out rounded-md p-1 -mt-1 -mr-1">
                        <x-heroicon-s-x-mark class="h-6 w-6" />
                    </button>
                </div>

                <div class="space-y-8">

                    {{-- Informasi Umum Dokumen --}}
                    <section
                        class="p-6 rounded-xl shadow-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-s-document-text class="inline-block h-5 w-5 mr-2 text-gray-500" /> Informasi
                            Umum Dokumen
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-base">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Nama Event Utama</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold">
                                    {{ $documentData['event'] ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Tanggal Dokumen</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold">
                                    {{ isset($documentData['documents'][0]['doc_date']) ? Carbon\Carbon::parse($documentData['documents'][0]['doc_date'])->translatedFormat('d F Y') : '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Kota Dokumen</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold">
                                    {{ $documentData['documents'][0]['city'] ?? '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Status Dokumen</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold capitalize">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $documentData['documents'][0]['status'] === 'done' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                        {{ $documentData['documents'][0]['status'] ?? '-' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <dt class="text-gray-500 dark:text-gray-400">Perihal</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold">
                                    {{ $documentData['documents'][0]['subject'] ?? '-' }}
                                </dd>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <dt class="text-gray-500 dark:text-gray-400 mb-1">Penerima</dt>
                                <dd class="text-gray-800 dark:text-gray-300">
                                    @forelse ($documentData['recipients'] ?? [] as $recipient)
                                        <span
                                            class="inline-flex items-center px-3 py-1 mr-2 mb-2 rounded-full text-sm font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                            <x-heroicon-s-user class="h-4 w-4 mr-1.5" /> {{ $recipient['recipient'] }}
                                            @if ($recipient['recipient_position'])
                                                ({{ $recipient['recipient_position'] }})
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-gray-500">-</span>
                                    @endforelse
                                </dd>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <dt class="text-gray-500 dark:text-gray-400">Deskripsi Event</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold">
                                    {{ $documentData['description'] ?? '-' }}
                                </dd>
                            </div>
                        </div>
                    </section>

                    {{-- Detail Waktu & Lokasi --}}
                    <section
                        class="p-6 rounded-xl shadow-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-s-clock class="inline-block h-5 w-5 mr-2 text-gray-500" /> Waktu & Lokasi
                            Kegiatan
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-base">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Dimulai</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold">
                                    {{ isset($documentData['start_datetime']) ? Carbon\Carbon::parse($documentData['start_datetime'])->translatedFormat('d F Y, H:i') : '-' }}
                                    WIB
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Berakhir</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold">
                                    {{ isset($documentData['end_datetime']) ? Carbon\Carbon::parse($documentData['end_datetime'])->translatedFormat('d F Y, H:i') : '-' }}
                                    WIB
                                </dd>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                <dt class="text-gray-500 dark:text-gray-400">Lokasi Kegiatan</dt>
                                <dd class="text-gray-800 dark:text-gray-300 font-semibold">
                                    {{ $documentData['location'] ?? '-' }}
                                </dd>
                            </div>
                        </div>
                    </section>

                    {{-- Jadwal / Rundown Kegiatan --}}
                    <section
                        class="p-6 rounded-xl shadow-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-s-list-bullet class="inline-block h-5 w-5 mr-2 text-gray-500" /> Jadwal Kegiatan
                        </h3>
                        <div class="space-y-4">
                            @forelse ($documentData['schedules'] ?? [] as $schedule)
                                <div
                                    class="p-4 rounded-lg bg-gray-100 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $schedule['description'] ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Pukul
                                        {{ $schedule['start_time'] ? Carbon\Carbon::parse($schedule['start_time'])->format('H:i') : 'N/A' }}
                                        -
                                        {{ $schedule['end_time'] ? Carbon\Carbon::parse($schedule['end_time'])->format('H:i') : 'N/A' }}
                                        WIB
                                        @if ($schedule['duration'])
                                            (Durasi: {{ $schedule['duration'] }})
                                        @endif
                                    </p>
                                </div>
                            @empty
                                <div
                                    class="text-center text-gray-500 dark:text-gray-400 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                    <p class="font-medium">Tidak ada jadwal kegiatan yang tercatat.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    {{-- Informasi Penanda Tangan --}}
                    <section
                        class="p-6 rounded-xl shadow-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-s-pencil-square class="inline-block h-5 w-5 mr-2 text-gray-500" /> Penanda
                            Tangan
                        </h3>
                        <div class="space-y-4">
                            @forelse ($documentData['documents'][0]['signature_blocks'] ?? [] as $signer)
                                {{-- Accessing from documents[0] --}}
                                <div
                                    class="p-4 rounded-lg bg-gray-100 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $signer['name'] ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $signer['position'] ?? 'N/A' }}</p>
                                </div>
                            @empty
                                <div
                                    class="text-center text-gray-500 dark:text-gray-400 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                    <p class="font-medium">Tidak ada data penanda tangan yang ditemukan.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                </div>

                {{-- Footer Modal --}}
                <div
                    class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'invitation-document-detail-modal')">
                        Tutup
                    </x-secondary-button>
                </div>
            </div>
        @else
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                <p>Memuat detail dokumen...</p>
            </div>
        @endif
    </x-modal>
</div>
