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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($this->events as $recurringEvent)
                {{-- Event Card --}}
                <a wire:click="$dispatch('showEventDetails', { eventId: {{ $recurringEvent->id }} })"
                    class="group cursor-pointer bg-white rounded-2xl overflow-hidden relative shadow-sm hover:shadow-xl hover:shadow-amber-500/10 transition-all duration-300 ease-out hover:-translate-y-1 h-[480px] w-full flex flex-col border border-gray-100 ring-1 ring-gray-100 hover:ring-amber-200">
                    
                    {{-- Event Badge --}}
                    <span class="absolute top-3 right-3 bg-white/95 backdrop-blur-sm text-[#825700] font-bold px-3 py-1 rounded-lg text-[10px] uppercase tracking-wider shadow-sm z-10 border border-amber-100">
                        {{ $recurringEvent->event->eventCategory->name }}
                    </span>

                    {{-- Event Visual Header --}}
                    <div class="h-48 w-full overflow-hidden relative shrink-0">
                        <div class="bg-gradient-to-br from-[#FFD24C] to-[#825700] h-full w-full flex items-center justify-center relative overflow-hidden group-hover:brightness-110 transition-all duration-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white/90 drop-shadow-lg transform transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Event Content --}}
                    <div class="p-5 flex flex-col flex-grow relative">
                        {{-- Date & Time - New Layout --}}
                        <div class="flex items-center gap-2 mb-3">
                            <div class="flex items-center text-amber-700 text-xs font-bold uppercase tracking-wide bg-amber-50 px-2 py-1 rounded-md border border-amber-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-amber-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ $recurringEvent->date->translatedFormat('d M') }}
                            </div>
                            <div class="flex items-center text-gray-400 text-xs font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $recurringEvent->time_start->format('H:i') }} WIB
                            </div>
                        </div>

                        {{-- Event Title --}}
                        <h3 class="text-[17px] font-bold text-gray-900 mb-2 leading-tight line-clamp-2 group-hover:text-[#825700] transition-colors h-[44px]">
                            {{ $recurringEvent->event->name }}
                        </h3>

                        {{-- Event Description --}}
                        <p class="text-gray-500 text-sm leading-relaxed line-clamp-3 mb-4 flex-grow">
                            {{ $recurringEvent->event->description }}
                        </p>

                        {{-- Footer: Location --}}
                        <div class="mt-auto pt-4 border-t border-gray-50 flex items-center text-gray-500 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 flex-shrink-0 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate font-medium text-xs text-gray-600 w-full">
                                @foreach ($recurringEvent->event->locations as $location)
                                    {{ $location->name }}@if (!$loop->last), @endif
                                @endforeach
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                {{-- Empty State --}}
                <div class="col-span-full">
                    <div class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm">
                        <div class="mx-auto mb-6 bg-amber-50 w-20 h-20 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#825700]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Belum Ada Kegiatan</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto leading-relaxed">
                            Saat ini belum ada kegiatan yang dijadwalkan. Silakan cek kembali nanti atau lihat arsip kegiatan kami.
                        </p>
                        <a href="{{ route('events.index') }}" wire:navigate
                            class="inline-flex items-center justify-center px-6 py-3 bg-[#FFD24C] hover:bg-[#FEC006] text-[#825700] font-bold rounded-xl transition-all duration-300 shadow-lg shadow-amber-200 hover:translate-y-[-2px]">
                            Lihat Semua Kegiatan
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
