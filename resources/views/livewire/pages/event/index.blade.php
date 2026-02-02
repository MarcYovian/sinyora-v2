<div class="font-sans antialiased text-gray-900">
    <!-- Hero Section -->
    <section class="relative bg-cover bg-center bg-no-repeat py-32 px-6"
        style="background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('{{ asset('images/1.jpg') }}');">
        <div class="container mx-auto text-center text-white max-w-4xl">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 leading-tight">
                Kalender Kegiatan Kapel
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
                Temukan jadwal kegiatan, perayaan liturgi, dan acara khusus di Kapel St. Yohanes Rasul
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#calendar-section"
                    class="inline-flex items-center px-6 py-3 bg-[#FFD24C] hover:bg-[#FEC006] text-[#825700] font-semibold rounded-full shadow-lg transition-colors duration-300">
                    Lihat Kalender
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="#upcoming-events"
                    class="inline-flex items-center px-6 py-3 border border-white text-white hover:bg-white/10 font-medium rounded-full transition-colors duration-300">
                    Kegiatan Mendatang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <button wire:click="create"
                    class="inline-flex items-center px-6 py-3 bg-white hover:bg-gray-200 text-gray-800 font-semibold rounded-full shadow-lg transition-colors duration-300">
                    Ajukan Kegiatan
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-16 max-w-7xl">
        <!-- Calendar Section -->
        <section id="calendar-section" class="mb-20">
            <div class="relative mb-12">
                <h1
                    class="absolute -top-10 left-0 text-gray-200 text-5xl sm:text-8xl md:text-9xl font-bold tracking-wide opacity-20 z-0">
                    KALENDER
                </h1>
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                        Kalender Kegiatan
                    </h2>
                    <p class="text-gray-600 text-lg max-w-3xl">
                        Jelajahi jadwal kegiatan dan perayaan liturgi Kapel St. Yohanes Rasul
                    </p>
                </div>
            </div>

            <livewire:pages.event.calender lazy />
        </section>

        <!-- Upcoming Events -->
        <livewire:pages.event.upcoming-events lazy />
    </div>

    <x-modal name="proposal-modal" focusable>
        <div class="p-4 sm:p-6">
            <livewire:pages.event.proposal-form />
        </div>
    </x-modal>

    <livewire:pages.event.event-details-modal />
</div>
