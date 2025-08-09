<div class="bg-white p-4 rounded-lg shadow-sm font-sans">
    @php
        // Tentukan tanggal awal dan akhir untuk grid kalender.
        // Grid akan selalu menampilkan 6 minggu agar tingginya konsisten.
        $date = \Carbon\Carbon::create($year, $month, 1);
        $startOfGrid = $date->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
        $endOfGrid = $date->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);
        $dateRange = \Carbon\CarbonPeriod::create($startOfGrid, $endOfGrid);
    @endphp

    {{-- Header Nama Hari --}}
    <div class="grid grid-cols-7 gap-px text-center text-sm font-semibold text-gray-500 pb-2 border-b">
        @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayName)
            <div>{{ $dayName }}</div>
        @endforeach
    </div>

    {{-- Grid Kalender --}}
    <div class="grid grid-cols-7 grid-rows-6 gap-px">
        @foreach ($dateRange as $day)
            @php
                $isToday = $day->isToday();
                $isCurrentMonth = $day->month == $month;
                $dayString = $day->toDateString();
            @endphp

            {{-- Setiap Sel Tanggal --}}
            <div
                class="relative flex flex-col p-2 transition-colors duration-150 {{ $isCurrentMonth ? 'bg-white hover:bg-gray-50' : 'bg-gray-50/80 text-gray-400' }}">
                {{-- Nomor Tanggal --}}
                <time datetime="{{ $dayString }}"
                    class="flex items-center justify-center text-sm font-bold w-8 h-8 rounded-full {{ $isToday ? 'bg-teal-600 text-white' : ($isCurrentMonth ? 'text-gray-700' : 'text-gray-400') }}">
                    {{ $day->day }}
                </time>

                {{-- Kontainer Acara (scrollable) --}}
                <div class="mt-2 flex-1 overflow-y-auto text-xs space-y-1.5 pr-1">
                    @if (isset($eventsByDay[$dayString]))
                        {{-- Batasi hanya 2 acara yang terlihat, sisanya disembunyikan --}}
                        @foreach (collect($eventsByDay[$dayString])->take(2) as $recurrence)
                            @php
                                $event = $recurrence->event;
                                $categoryColor = $event->eventCategory->color ?? '#6b7280'; // Default abu-abu
                                $startTime = \Carbon\Carbon::parse($recurrence->getRawOriginal('time_start'))->format(
                                    'H:i',
                                );
                            @endphp
                            <div wire:click="$dispatch('showEventDetails', { eventId: {{ $recurrence->id }} })"
                                class="flex items-center p-1.5 rounded-lg cursor-pointer transition-transform hover:scale-105"
                                style="background-color: {{ $categoryColor }}20;" {{-- Warna kategori dengan transparansi 20% --}}>
                                {{-- Titik penanda warna --}}
                                <div class="w-1.5 h-1.5 mr-2 rounded-full flex-shrink-0"
                                    style="background-color: {{ $categoryColor }};"></div>
                                {{-- Detail Acara --}}
                                <p class="truncate font-semibold" style="color: {{ $categoryColor }};">
                                    <span class="font-normal text-gray-600">{{ $startTime }}</span>
                                    {{ $event->name }}
                                </p>
                            </div>
                        @endforeach

                        {{-- Indikator jika ada lebih banyak acara --}}
                        @if (count($eventsByDay[$dayString]) > 2)
                            <p class="text-gray-500 font-medium pl-1 mt-1">+{{ count($eventsByDay[$dayString]) - 2 }}
                                acara lagi</p>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
