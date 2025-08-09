<div x-data="{
    init() {
            // Fungsi untuk memperbarui posisi indikator waktu saat ini.
            this.updateTimeIndicator();
            setInterval(() => this.updateTimeIndicator(), 60000); // Perbarui setiap menit
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

    {{-- Header Kalender (Nama Hari & Tanggal) --}}
    <div class="flex flex-none items-center justify-center border-b border-gray-200 p-4">
        <div class="text-center">
            <p class="text-3xl font-bold {{ $date->isToday() ? 'text-teal-600' : 'text-gray-900' }}">
                {{ $date->locale('id')->dayName }}
            </p>
        </div>
    </div>

    {{-- Body Kalender (Scrollable) --}}
    <div class="flex-1 overflow-auto">
        <div class="flex h-full">
            {{-- Kolom Timeline (Jam) - Sticky --}}
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

            {{-- Grid Acara (Hanya satu kolom) --}}
            <div class="flex-1 grid grid-cols-1">
                <div class="relative border-r border-gray-100">
                    {{-- Latar belakang garis per jam --}}
                    @for ($hour = 0; $hour < 24; $hour++)
                        <div class="h-[60px] border-b border-gray-100"></div>
                    @endfor

                    {{-- Indikator Waktu Saat Ini (Hanya jika hari ini) --}}
                    @if ($date->isToday())
                        <div x-ref="timeIndicator"
                            class="absolute left-0 right-0 h-0.5 bg-red-500 z-10 flex items-center">
                            <div class="w-2 h-2 bg-red-500 rounded-full -ml-1"></div>
                        </div>
                    @endif

                    {{-- Render Acara --}}
                    @foreach ($eventsByDay as $recurrence)
                        @php
                            $event = $recurrence->event;
                            $categoryColor = $event->eventCategory->color ?? '#4a5568'; // Default color abu-abu

                            // Gabungkan semua lokasi menjadi satu string
                            $locations = $event->locations
                                ->pluck('name')
                                ->merge($event->customLocations->pluck('address'))
                                ->implode(', ');
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
                            $height = max($durationInMinutes, 25);

                            $startTime = $startCarbon->format('H:i');
                            $endTime = $endCarbon->format('H:i');
                        @endphp

                        <div wire:click="$dispatch('showEventDetails', { eventId: {{ $recurrence->id }} })"
                            class="absolute left-1.5 right-1.5 z-[15] bg-white text-gray-800 rounded-lg p-2 flex flex-col cursor-pointer overflow-hidden shadow-md transition-all duration-200 hover:shadow-lg hover:scale-[1.02]"
                            style="top: {{ $topPosition }}px; height: {{ $height }}px; border-left: 5px solid {{ $categoryColor }};">

                            {{-- Nama Acara --}}
                            <p class="font-bold truncate text-sm" style="color: {{ $categoryColor }};">
                                {{ $event->name }}</p>

                            {{-- Waktu --}}
                            <p class="text-gray-600 text-xs font-medium mt-1">{{ $startTime }} -
                                {{ $endTime }}</p>

                            {{-- Detail Lokasi & Organisasi --}}
                            <div class="mt-auto text-xs space-y-1 pt-1">
                                @if ($locations)
                                    <div class="flex items-center text-gray-500 truncate">
                                        {{-- SVG Ikon Lokasi --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 flex-shrink-0"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="truncate">{{ $locations }}</span>
                                    </div>
                                @endif

                                @if ($event->organization)
                                    <div class="flex items-center text-gray-500 truncate">
                                        {{-- SVG Ikon Organisasi --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 flex-shrink-0"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                        </svg>
                                        <span class="truncate">{{ $event->organization->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
