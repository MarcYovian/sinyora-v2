<div class="bg-white rounded-lg font-sans">
    @php
        $date = \Carbon\Carbon::create($year, $month, 1);
        $startOfGrid = $date->copy()->startOfMonth()->startOfWeek(\Carbon\CarbonInterface::SUNDAY);
        $endOfGrid = $date->copy()->endOfMonth()->endOfWeek(\Carbon\CarbonInterface::SATURDAY);
        $dateRange = \Carbon\CarbonPeriod::create($startOfGrid, $endOfGrid);
    @endphp

    {{-- Header Nama Hari --}}
    <div class="grid grid-cols-7 gap-px text-center text-xs sm:text-sm font-semibold text-gray-500 pb-2 border-b">
        {{-- CATATAN PENGEMBANG: Nama hari diubah menjadi 1 huruf di layar < 400px (custom class 'xs:') --}}
        @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dayName)
            <div>
                <span class="hidden xs:inline">{{ substr($dayName, 0, 1) }}</span>
                <span class="xs:hidden">{{ $dayName }}</span>
            </div>
        @endforeach
    </div>

    {{-- Grid Kalender --}}
    <div class="grid grid-cols-7 grid-rows-6 gap-px min-h-[75vh]">
        @foreach ($dateRange as $day)
            @php
                $isToday = $day->isToday();
                $isCurrentMonth = $day->month == $month;
                $dayString = $day->toDateString();
            @endphp

            <div
                class="relative flex flex-col p-1 sm:p-2 transition-colors duration-150 {{ $isCurrentMonth ? 'bg-white hover:bg-gray-50' : 'bg-gray-50/80 text-gray-400' }}">
                <time datetime="{{ $dayString }}"
                    class="flex items-center justify-center text-xs sm:text-sm font-bold w-6 h-6 sm:w-8 sm:h-8 rounded-full {{ $isToday ? 'bg-teal-600 text-white' : ($isCurrentMonth ? 'text-gray-700' : 'text-gray-400') }}">
                    {{ $day->day }}
                </time>

                <div class="mt-2 flex-1 overflow-y-auto text-xs space-y-1.5 pr-1">
                    @if (isset($eventsByDay[$dayString]))
                        @foreach (collect($eventsByDay[$dayString])->take(2) as $recurrence)
                            @php
                                $event = $recurrence->event;
                                $categoryColor = $event->eventCategory->color ?? '#6b7280';
                                $locationBackgroundColor = $event->computed_background_color ?? '#f3f4f6';
                                $startTime = \Carbon\Carbon::parse($recurrence->getRawOriginal('time_start'))->format(
                                    'H:i',
                                );
                            @endphp
                            <div wire:click="$dispatch('showEventDetails', { eventId: {{ $recurrence->id }} })"
                                class="flex items-center p-1 sm:p-1.5 rounded-lg cursor-pointer transition-transform hover:scale-105"
                                style="background-color: {{ $locationBackgroundColor }}40;">
                                <div class="w-1.5 h-1.5 mr-1 sm:mr-2 rounded-full flex-shrink-0"
                                    style="background-color: {{ $categoryColor }};"></div>
                                <p class="truncate font-semibold" style="color: {{ $categoryColor }};">
                                    <span class="hidden sm:inline font-normal text-gray-600">{{ $startTime }}</span>
                                    <span class="sm:hidden">{{ $event->name }}</span>
                                    <span class="hidden sm:inline">{{ $event->name }}</span>
                                </p>
                            </div>
                        @endforeach

                        @if (count($eventsByDay[$dayString]) > 2)
                            <p class="text-gray-500 font-medium pl-1 mt-1 text-[10px] sm:text-xs">
                                +{{ count($eventsByDay[$dayString]) - 2 }} lagi</p>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
