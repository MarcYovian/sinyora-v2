<div id="upcoming-events" class="scroll-mt-24">
    <div class="relative mb-12">
        <h1 class="absolute -top-10 left-0 text-gray-200 text-5xl sm:text-8xl md:text-9xl font-bold tracking-wide opacity-20 z-0">
            KEGIATAN
        </h1>
        <div class="relative z-10">
            <div class="h-8 w-48 bg-gray-200 rounded animate-pulse mb-4"></div>
            <div class="h-4 w-96 max-w-full bg-gray-200 rounded animate-pulse"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @for ($i = 0; $i < 6; $i++)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                {{-- Date Badge --}}
                <div class="flex items-start p-4">
                    <div class="flex-shrink-0 bg-[#FFD24C]/20 rounded-lg p-3 text-center mr-4">
                        <div class="h-8 w-10 bg-gray-200 rounded animate-pulse mb-1"></div>
                        <div class="h-3 w-8 bg-gray-200 rounded animate-pulse"></div>
                    </div>
                    
                    {{-- Content --}}
                    <div class="flex-grow">
                        <div class="h-5 w-3/4 bg-gray-200 rounded animate-pulse mb-2"></div>
                        <div class="h-4 w-1/2 bg-gray-200 rounded animate-pulse mb-2"></div>
                        <div class="flex items-center gap-2">
                            <div class="h-3 w-20 bg-gray-200 rounded animate-pulse"></div>
                            <div class="h-3 w-16 bg-gray-200 rounded animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>
