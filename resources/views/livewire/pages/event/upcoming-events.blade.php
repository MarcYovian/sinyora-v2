<section id="upcoming-events" class="pb-16">
    <div class="container mx-auto max-w-7xl">
        {{-- Section Header --}}
        <div class="mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2 relative inline-block after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-1/2 after:h-[3px] after:bg-[#FFD24C] after:rounded-full">
                Kegiatan Mendatang
            </h2>
            <p class="text-gray-500 text-base md:text-lg max-w-xl">
                Jadwal kegiatan dan acara penting di Kapel St. Yohanes Rasul
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($events as $recurringEvent)
                <a wire:click="$dispatch('showEventDetails', { eventId: {{ $recurringEvent->id }} })"
                    class="group cursor-pointer bg-white rounded-xl overflow-hidden relative shadow-md hover:shadow-xl transition-all duration-300 ease-out hover:-translate-y-1 h-full flex flex-col max-w-[400px] mx-auto md:max-w-none">
                    
                    {{-- Event Badge --}}
                    <span class="absolute top-4 right-4 bg-[#FFD24C] text-[#825700] font-semibold px-3 py-1 rounded-full text-xs shadow-sm z-10">
                        {{ $recurringEvent->event->eventCategory->name }}
                    </span>

                    {{-- Event Image --}}
                    <div class="h-40 overflow-hidden relative">
                        @if ($recurringEvent->event->image)
                            <img src="{{ Storage::url($recurringEvent->event->image) }}"
                                alt="{{ $recurringEvent->event->name }}" 
                                class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                        @else
                            <div class="bg-gradient-to-br from-[#FFD24C] to-[#825700] h-full w-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Event Content --}}
                    <div class="p-5 flex-grow flex flex-col">
                        {{-- Event Date --}}
                        <div class="flex items-center text-gray-500 text-sm mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $recurringEvent->date->translatedFormat('l, d F Y') }} •
                            {{ $recurringEvent->time_start->format('H:i') }} WIB
                        </div>

                        {{-- Event Title --}}
                        <h3 class="text-xl font-semibold text-gray-800 mb-3 leading-tight">
                            {{ $recurringEvent->event->name }}
                        </h3>

                        {{-- Event Description --}}
                        <p class="text-gray-600 text-[15px] mb-4 line-clamp-3 flex-grow">
                            {{ Str::limit($recurringEvent->event->description, 120) }}
                        </p>

                        {{-- Event Location --}}
                        <div class="flex items-center text-gray-500 text-sm mt-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate">
                                @foreach ($recurringEvent->event->locations as $location)
                                    {{ $location->name }}@if (!$loop->last), @endif
                                @endforeach
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                {{-- Empty State --}}
                <div class="bg-white rounded-xl p-8 text-center col-span-full">
                    <div class="mx-auto mb-4 bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Tidak Ada Acara Mendatang</h3>
                    <p class="text-gray-500 mb-4 max-w-md mx-auto">
                        Saat ini tidak ada acara yang dijadwalkan. Silakan periksa kembali nanti untuk update terbaru.
                    </p>
                    <a href="{{ route('events.index') }}" wire:navigate
                        class="inline-flex items-center justify-center px-4 py-2 bg-[#FFD24C] hover:bg-[#FEC006] text-[#825700] font-medium rounded-lg transition-all duration-200 hover:-translate-y-0.5">
                        Lihat Semua Acara
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>
