@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        /* Custom Calendar Styles */
        .fc {
            font-family: 'Inter', sans-serif;
        }

        .fc-toolbar-title {
            font-weight: 600;
            color: #282834;
        }

        .fc-button {
            background-color: #FFD24C !important;
            border-color: #FFD24C !important;
            color: #825700 !important;
            font-weight: 500;
            text-transform: capitalize;
            border-radius: 8px !important;
            padding: 6px 12px !important;
        }

        .fc-button:hover {
            background-color: #FEC006 !important;
            border-color: #FEC006 !important;
        }

        .fc-button-active {
            background-color: #825700 !important;
            border-color: #825700 !important;
            color: white !important;
        }

        .fc-daygrid-event {
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.9rem;
            border: none;
        }

        .fc-event-title {
            font-weight: 500;
        }

        .fc-daygrid-day-number {
            color: #4B5563;
            font-weight: 500;
        }

        .fc-daygrid-day.fc-day-today {
            background-color: #FFD24C20 !important;
        }

        .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            color: #825700;
            font-weight: 600;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .fc-toolbar {
                flex-direction: column;
                gap: 12px;
            }

            .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                width: 100%;
            }
        }
    </style>
@endpush

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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                        fill="currentColor">
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
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-16 max-w-7xl">
        <!-- Calendar Section -->
        <section id="calendar-section" class="mb-20">
            <div class="relative mb-12">
                <h1
                    class="absolute -top-10 left-0 text-gray-200 text-7xl sm:text-8xl md:text-9xl font-bold tracking-wide opacity-20 z-0">
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

            <div class="bg-white rounded-xl shadow-lg overflow-hidden p-6">
                <div id="calendar" class="w-full"></div>
            </div>
        </section>

        <!-- Upcoming Events -->
        <livewire:pages.event.upcoming-events />

        <!-- All Events -->
        {{-- <livewire:pages.event.all-events /> --}}
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.min.js"></script>
    <script>
        // Deklarasikan variabel di level tertinggi (window) hanya sekali
        window.calendarManager = {
            instance: null,

            init: function() {
                // Hancurkan instance sebelumnya jika ada
                this.destroy();

                const calendarEl = document.getElementById('calendar');
                if (!calendarEl) return;

                this.instance = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'id',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    buttonText: {
                        today: 'Hari Ini',
                        month: 'Bulan',
                        week: 'Minggu',
                        day: 'Hari'
                    },
                    events: @json($events),
                    eventClick: function(info) {
                        alert('Event: ' + info.event.title);
                        info.jsEvent.preventDefault();
                    }
                });

                this.instance.render();
            },

            destroy: function() {
                if (this.instance && typeof this.instance.destroy === 'function') {
                    this.instance.destroy();
                }
                this.instance = null;
            }
        };

        // Inisialisasi pertama kali
        document.addEventListener('DOMContentLoaded', function() {
            window.calendarManager.init();
        });

        // Untuk Livewire
        document.addEventListener('livewire:init', function() {
            Livewire.on('rendered', function() {
                window.calendarManager.init();
            });
        });

        document.addEventListener('livewire:navigated', function() {
            // Gunakan timeout kecil untuk memastikan DOM siap
            setTimeout(function() {
                window.calendarManager.init();
            }, 50);
        });
    </script>
@endpush
