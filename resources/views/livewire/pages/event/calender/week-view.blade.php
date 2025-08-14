<div x-data="{
    init() {
            this.updateTimeIndicator();
            setInterval(() => this.updateTimeIndicator(), 60000);
            if (window.innerWidth < 768) {
                const todayEl = this.$refs.today;
                if (todayEl) {
                    todayEl.scrollIntoView({ behavior: 'auto', block: 'start', inline: 'center' });
                }
            }
        },
        updateTimeIndicator() {
            const now = new Date();
            const topPosition = now.getHours() * 60 + now.getMinutes();
            const indicator = this.$refs.timeIndicator;
            if (indicator) {
                indicator.style.top = `${topPosition}px`;
            }
        }
}" class="flex h-[85vh] flex-col bg-white font-sans">

    {{-- Header Kalender --}}
    <div class="flex flex-none border-b border-gray-200">
        <div class="w-16 flex-none bg-white"></div>
        <div class="flex-1 grid grid-cols-7">
            @for ($i = 0; $i < 7; $i++)
                @php
                    $day = $startOfWeek->clone()->addDays($i);
                    $isToday = $day->isToday();
                @endphp
                <div class="flex items-center justify-center py-3">
                    <div class="text-center">
                        <p class="text-sm font-medium {{ $isToday ? 'text-teal-600' : 'text-gray-500' }}">
                            {{ $day->locale('id')->minDayName }}
                        </p>
                        <p class="text-2xl font-bold mt-1 {{ $isToday ? 'text-teal-600' : 'text-gray-800' }}">
                            {{ $day->day }}
                        </p>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    {{-- Body Kalender (Scrollable) --}}
    <div class="flex-1 overflow-auto">
        <div class="flex h-full">
            {{-- Kolom Timeline (Jam) --}}
            <div class="w-16 flex-none bg-white z-10">
                <div class="flex flex-col">
                    @for ($hour = 0; $hour < 24; $hour++)
                        <div class="h-[60px] relative border-b border-gray-100">
                            <span
                                class="absolute -top-2.5 right-1 text-xs text-gray-400">{{ sprintf('%02d:00', $hour) }}</span>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Grid Acara --}}
            <div class="flex-1 grid grid-cols-7">
                @for ($i = 0; $i < 7; $i++)
                    @php
                        $day = $startOfWeek->clone()->addDays($i);
                        $dayString = $day->toDateString();
                        $isToday = $day->isToday();
                    @endphp

                    <div class="relative border-r border-gray-100"
                        @if ($isToday) x-ref="today" @endif>
                        @for ($hour = 0; $hour < 24; $hour++)
                            <div class="h-[60px] border-b border-gray-100"></div>
                        @endfor

                        @if ($isToday)
                            <div x-ref="timeIndicator"
                                class="absolute left-0 right-0 h-0.5 bg-red-500 z-10 flex items-center">
                                <div class="w-2 h-2 bg-red-500 rounded-full -ml-1"></div>
                            </div>
                        @endif

                        @if (isset($eventsByDay[$dayString]))
                            @foreach ($eventsByDay[$dayString] as $recurrence)
                                @if ($recurrence->is_grouped_master ?? false)
                                    @php
                                        $event = $recurrence->event;
                                        $categoryColor = $event->eventCategory->color ?? '#4a5568';

                                        // Gunakan GRADIENT untuk border jika ada beberapa lokasi
                                        $locationBorderImage =
                                            $recurrence->computed_background_gradient ??
                                            'linear-gradient(to right, #f3f4f6, #f3f4f6)';

                                        // Perhitungan posisi & tinggi tetap sama
                                        $startCarbon = $recurrence->date
                                            ->copy()
                                            ->setTimeFromTimeString($recurrence->getRawOriginal('time_start'));
                                        $endCarbon = $recurrence->date
                                            ->copy()
                                            ->setTimeFromTimeString($recurrence->getRawOriginal('time_end'));
                                        if ($endCarbon->isBefore($startCarbon)) {
                                            $endCarbon->addDay();
                                        }
                                        $topPosition = $startCarbon->hour * 60 + $startCarbon->minute;
                                        $durationInMinutes = $startCarbon->diffInMinutes($endCarbon);
                                        $height = max($durationInMinutes, 60); // Tingkatkan tinggi minimal untuk ruang ekstra
                                        $startTime = $startCarbon->format('H:i');
                                        $endTime = $endCarbon->format('H:i');
                                    @endphp

                                    <div wire:click="$dispatch('showEventDetails', { eventId: {{ $recurrence->id }} })"
                                        class="absolute right-0 z-[15] bg-white text-gray-800 rounded-lg p-2.5 flex flex-col cursor-pointer overflow-hidden shadow-md transition-all duration-200 hover:shadow-xl hover:z-[25]"
                                        style="
                                            top: {{ $topPosition }}px;
                                            height: {{ $height }}px;
                                            /* Menggunakan border-image untuk gradient */
                                            border-left-width: 5px;
                                            border-image: {{ $locationBorderImage }};
                                            border-image-slice: 1;
                                            /* Properti layout dari Trait */
                                            width: calc({{ $recurrence->layout_width ?? 100 }}% - 10px);
                                            left: calc({{ $recurrence->layout_left ?? 0 }}% + 5px);
                                            z-index: {{ $recurrence->layout_zindex ?? 15 }};
                                        ">

                                        {{-- Bagian Atas: Nama & Waktu --}}
                                        <div class="flex-shrink-0">
                                            <p class="font-bold truncate text-sm" style="color: {{ $categoryColor }};">
                                                {{ $event->name }}
                                            </p>
                                            <p class="text-gray-600 text-xs font-medium mt-0.5">
                                                {{ $startTime }} - {{ $endTime }}
                                            </p>
                                        </div>

                                        {{-- Bagian Bawah: Daftar Lokasi (Informatif) --}}
                                        <div class="mt-auto text-xs space-y-1 pt-1.5 overflow-y-auto">
                                            {{-- Loop melalui lokasi yang sudah digabungkan --}}
                                            @foreach ($recurrence->grouped_locations as $location)
                                                <div class="flex items-center text-gray-700 truncate">
                                                    {{-- Titik warna sesuai warna asli lokasi --}}
                                                    <div class="w-2 h-2 rounded-full mr-2 flex-shrink-0"
                                                        style="background-color: {{ $location->color ?? '#cbd5e1' }};">
                                                    </div>
                                                    {{-- Nama lokasi atau alamat --}}
                                                    <span
                                                        class="truncate">{{ $location->name ?? $location->address }}</span>
                                                </div>
                                            @endforeach

                                            {{-- Indikator jika terlalu banyak lokasi untuk ditampilkan --}}
                                            @if ($recurrence->grouped_locations->count() > 3 && $height < 120)
                                                <p class="text-gray-500 text-xs mt-1">
                                                    +{{ $recurrence->grouped_locations->count() - 3 }} lokasi lain</p>
                                            @endif

                                            @if ($event->organization)
                                                <div class="flex items-center text-gray-600 truncate mt-1">
                                                    {{-- Ikon Organisasi (Gedung) --}}
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-3.5 w-3.5 mr-2 flex-shrink-0" viewBox="0 0 20 20"
                                                        fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2 0h2v2h-2V9zm2-4h-2v2h2V5z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span
                                                        class="truncate font-medium">{{ $event->organization->name }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
