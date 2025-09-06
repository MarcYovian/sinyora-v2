<div x-data="{
    init() {
            this.updateTimeIndicator();
            setInterval(() => this.updateTimeIndicator(), 60000);
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
    <div class="flex flex-none items-center justify-center border-b border-gray-200 p-4">
        <div class="text-center">
            <p class="text-xl sm:text-3xl font-bold {{ $date->isToday() ? 'text-teal-600' : 'text-gray-900' }}">
                {{ $date->locale('id')->dayName }}
            </p>
        </div>
    </div>
    <div class="flex-1 overflow-auto">
        <div class="flex h-full">
            {{-- CATATAN PENGEMBANG: Lebar kolom jam dibuat lebih kecil di mobile --}}
            <div class="w-12 sm:w-16 flex-none bg-white z-10">
                <div class="flex flex-col">
                    @for ($hour = 0; $hour < 24; $hour++)
                        <div class="h-[60px] relative border-b border-gray-100">
                            <span
                                class="absolute -top-2.5 right-1 text-xs text-gray-400">{{ sprintf('%02d:00', $hour) }}</span>
                        </div>
                    @endfor
                </div>
            </div>
            <div class="flex-1 grid grid-cols-1">
                <div class="relative border-r border-gray-100 h-[1440px]">
                    @if ($date->isToday())
                        <div x-ref="timeIndicator"
                            class="absolute left-0 right-0 h-0.5 bg-red-500 z-10 flex items-center">
                            <div class="w-2 h-2 bg-red-500 rounded-full -ml-1"></div>
                        </div>
                    @endif
                    @foreach ($eventsByDay as $recurrence)
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
                            @endphp
                            <div wire:click="$dispatch('showEventDetails', { eventId: {{ $recurrence->id }} })"
                                class="absolute right-0 bg-white text-gray-800 rounded-lg p-2.5 flex flex-col cursor-pointer overflow-hidden shadow-md"
                                style="top: {{ $topPosition }}px; height: {{ $height }}px; border-left-width: 5px; border-image: {{ $locationBorderImage }}; border-image-slice: 1; width: calc({{ $recurrence->layout_width ?? 100 }}% - 10px); left: calc({{ $recurrence->layout_left ?? 0 }}% + 5px); z-index: {{ $recurrence->layout_zindex ?? 15 }};">
                                <p class="font-bold truncate text-sm" style="color: {{ $categoryColor }};">
                                    {{ $event->name }}</p>
                                <p class="text-gray-600 text-xs mt-0.5">{{ $startCarbon->format('H:i') }} -
                                    {{ $endCarbon->format('H:i') }}</p>
                                <div class="mt-auto text-xs space-y-1 pt-1.5 overflow-y-auto">
                                    @foreach ($recurrence->grouped_locations as $location)
                                        <div class="flex items-center text-gray-700 truncate">
                                            <div class="w-2 h-2 rounded-full mr-2 flex-shrink-0"
                                                style="background-color: {{ $location->color ?? '#cbd5e1' }};"></div>
                                            <span class="truncate">{{ $location->name ?? $location->address }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
