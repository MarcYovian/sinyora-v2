<x-modal name="event-details-modal" :maxWidth="'2xl'" focusable>
    {{-- Hanya render konten jika eventRecurrence sudah ada --}}
    @if ($eventRecurrence)
        @php
            // Siapkan variabel untuk kemudahan akses
            $event = $eventRecurrence->event;
            $category = $event->eventCategory;
            $organization = $event->organization;

            // Dapatkan warna kategori dengan warna default
            $categoryColor = $category->color ?? '#6b7280';

            // Gabungkan semua lokasi menjadi satu koleksi
            $locations = $event->locations->pluck('name')->merge($event->customLocations->pluck('address'));

            // Format tanggal dan waktu
            $fullDate = \Carbon\Carbon::parse($eventRecurrence->date)->locale('id')->isoFormat('dddd, D MMMM YYYY');
            $startTime = \Carbon\Carbon::parse($eventRecurrence->getRawOriginal('time_start'))->format('H:i');
            $endTime = \Carbon\Carbon::parse($eventRecurrence->getRawOriginal('time_end'))->format('H:i');
        @endphp

        <div class="bg-white rounded-lg shadow-xl" x-on:click.outside="$dispatch('close')">
            {{-- HEADER MODAL DENGAN WARNA KATEGORI --}}
            <div class="p-5 rounded-t-lg" style="background-color: {{ $categoryColor }}20;">
                <div class="flex justify-between items-start">
                    <div>
                        {{-- Nama Kategori sebagai Tag --}}
                        <span class="text-sm font-semibold px-3 py-1 rounded-full"
                            style="color: {{ $categoryColor }}; background-color: {{ $categoryColor }}30;">
                            {{ $category->name ?? 'Tanpa Kategori' }}
                        </span>
                        {{-- Nama Acara --}}
                        <h2 class="text-2xl font-bold mt-2" style="color: {{ $categoryColor }};">
                            {{ $event->name }}
                        </h2>
                    </div>
                    {{-- Tombol Close --}}
                    <button x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- KONTEN UTAMA MODAL --}}
            <div class="p-6 space-y-6">
                {{-- Detail Waktu & Tanggal --}}
                <div class="flex items-start space-x-4">
                    {{-- Ikon Kalender --}}
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg"
                        style="background-color: {{ $categoryColor }}20;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" style="color: {{ $categoryColor }};"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Waktu & Tanggal</h3>
                        <p class="text-gray-600">{{ $fullDate }}</p>
                        <p class="text-gray-600">{{ $startTime }} - {{ $endTime }} WIB</p>
                    </div>
                </div>

                {{-- Detail Lokasi --}}
                @if ($locations->isNotEmpty())
                    <div class="flex items-start space-x-4">
                        {{-- Ikon Lokasi --}}
                        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg"
                            style="background-color: {{ $categoryColor }}20;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                style="color: {{ $categoryColor }};" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Lokasi</h3>
                            <ul class="list-disc list-inside text-gray-600">
                                @foreach ($locations as $location)
                                    <li>{{ $location }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Detail Penyelenggara --}}
                @if ($organization)
                    <div class="flex items-start space-x-4">
                        {{-- Ikon Organisasi --}}
                        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg"
                            style="background-color: {{ $categoryColor }}20;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                style="color: {{ $categoryColor }};" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Penyelenggara</h3>
                            <p class="text-gray-600">{{ $organization->name }}</p>
                        </div>
                    </div>
                @endif

                {{-- Deskripsi Acara --}}
                @if ($event->description)
                    <div class="border-t pt-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Deskripsi</h3>
                        <p class="text-gray-600 whitespace-pre-wrap">{{ $event->description }}</p>
                    </div>
                @endif
            </div>

            {{-- FOOTER MODAL --}}
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end space-x-3">
                <button type="button" x-on:click="$dispatch('close')"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Tutup
                </button>
            </div>
        </div>
    @endif
</x-modal>
