<div x-data="{
    // Fungsi untuk mensinkronkan scroll horizontal antara header dan body
    syncScroll() {
            this.$refs.headerContainer.scrollLeft = this.$refs.calendarBody.scrollLeft;
        },
        init() {
            this.updateTimeIndicator();
            setInterval(() => this.updateTimeIndicator(), 60000);
            // Scroll ke hari ini saat komponen dimuat
            this.$nextTick(() => {
                const todayEl = this.$refs.today;
                if (todayEl) {
                    todayEl.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'center' });
                }
            });
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

    {{-- 1. HEADER (Nama Hari) --}}
    {{-- Kontainer ini akan digulir secara horizontal oleh AlpineJS --}}
    <div class="flex flex-none border-b border-gray-200">
        {{-- Spacer untuk menyamai lebar kolom jam --}}
        <div class="w-14 flex-none border-r border-gray-200"></div>
        <div class="flex-1 overflow-hidden" x-ref="headerContainer">
            {{-- Grid nama hari yang lebar agar bisa di-scroll --}}
            <div class="grid grid-cols-7 min-w-[1050px] sm:min-w-[1260px]">
                @for ($i = 0; $i < 7; $i++)
                    @php
                        $day = $startOfWeek->clone()->addDays($i);
                        $isToday = $day->isToday();
                    @endphp
                    <div class="flex items-center justify-center py-3 border-r border-gray-100">
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
    </div>

    {{-- 2. BODY (Kontainer Scroll Utama) --}}
    {{-- Kontainer ini menangani scroll vertikal dan horizontal --}}
    <div class="flex-1 overflow-auto" x-ref="calendarBody" @scroll="syncScroll()">
        {{-- Konten yang lebar untuk memungkinkan scroll --}}
        <div class="flex min-w-[1050px] sm:min-w-[1260px]">
            {{-- 2a. Kolom Timeline (Jam) --}}
            {{-- Kolom ini sekarang berada di dalam kontainer scroll utama --}}
            <div class="w-14 flex-none bg-white z-10 border-r border-gray-200">
                <div class="flex flex-col">
                    @for ($hour = 0; $hour < 24; $hour++)
                        <div class="h-[60px] relative border-b border-gray-100">
                            <span
                                class="absolute -top-2.5 right-1 text-xs text-gray-400">{{ sprintf('%02d:00', $hour) }}</span>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- 2b. Grid Acara --}}
            <div class="flex-1 grid grid-cols-7">
                @for ($i = 0; $i < 7; $i++)
                    @php
                        $day = $startOfWeek->clone()->addDays($i);
                        $dayString = $day->toDateString();
                        $isToday = $day->isToday();
                    @endphp
                    <div class="relative border-r border-gray-100"
                        @if ($isToday) x-ref="today" @endif>
                        {{-- Garis-garis jam di latar belakang --}}
                        @for ($hour = 0; $hour < 24; $hour++)
                            <div class="h-[60px] border-b border-gray-100"></div>
                        @endfor

                        {{-- Indikator Waktu Saat Ini --}}
                        @if ($isToday)
                            <div x-ref="timeIndicator"
                                class="absolute left-0 right-0 h-0.5 bg-red-500 z-20 flex items-center">
                                <div class="w-2 h-2 bg-red-500 rounded-full -ml-1"></div>
                            </div>
                        @endif

                        {{-- Render Acara --}}
                        @if (isset($eventsByDay[$dayString]))
                            @foreach ($eventsByDay[$dayString] as $recurrence)
                                @if ($recurrence->is_grouped_master ?? false)
                                    @php
                                        $event = $recurrence->event;
                                        $categoryColor = $event->eventCategory->color ?? '#4a5568';
                                        $locationBorderImage =
                                            $recurrence->computed_background_gradient ??
                                            'linear-gradient(to right, #f3f4f6, #f3f4f6)';
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
                                        $height = max($startCarbon->diffInMinutes($endCarbon), 60);
                                        $startTime = $startCarbon->format('H:i');
                                        $endTime = $endCarbon->format('H:i');
                                    @endphp
                                    <div wire:click="$dispatch('showEventDetails', { eventId: {{ $recurrence->id }} })"
                                        class="absolute right-0 z-[15] bg-white text-gray-800 rounded-lg p-2.5 flex flex-col cursor-pointer overflow-hidden shadow-md transition-all duration-200 hover:shadow-xl hover:z-[25]"
                                        style="
                                            top: {{ $topPosition }}px;
                                            height: {{ $height }}px;
                                            border-left-width: 5px;
                                            border-image: {{ $locationBorderImage }};
                                            border-image-slice: 1;
                                            width: calc({{ $recurrence->layout_width ?? 100 }}% - 10px);
                                            left: calc({{ $recurrence->layout_left ?? 0 }}% + 5px);
                                            z-index: {{ $recurrence->layout_zindex ?? 15 }};
                                        ">
                                        <div class="flex-shrink-0">
                                            <p class="font-bold truncate text-sm" style="color: {{ $categoryColor }};">
                                                {{ $event->name }}</p>
                                            <p class="text-gray-600 text-xs font-medium mt-0.5">{{ $startTime }} -
                                                {{ $endTime }}</p>
                                        </div>
                                        <div class="mt-auto text-xs space-y-1 pt-1.5 overflow-y-auto">
                                            @foreach ($recurrence->grouped_locations as $location)
                                                <div class="flex items-center text-gray-700 truncate">
                                                    <div class="w-2 h-2 rounded-full mr-2 flex-shrink-0"
                                                        style="background-color: {{ $location->color ?? '#cbd5e1' }};">
                                                    </div>
                                                    <span
                                                        class="truncate">{{ $location->name ?? $location->address }}</span>
                                                </div>
                                            @endforeach
                                            @if ($event->organization)
                                                <div class="flex items-center text-gray-600 truncate mt-1">
                                                    <x-heroicon-s-building-office
                                                        class="h-3.5 w-3.5 mr-1.5 flex-shrink-0" />
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
