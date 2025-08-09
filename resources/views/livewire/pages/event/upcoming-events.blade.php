@push('styles')
    <style>
        /* Enhanced Event Card Styles */
        .event-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .event-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background-color: #FFD24C;
            color: #825700;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .event-image-container {
            height: 160px;
            overflow: hidden;
            position: relative;
        }

        .event-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .event-card:hover .event-image {
            transform: scale(1.05);
        }

        .event-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .event-date {
            display: flex;
            align-items: center;
            color: #6B7280;
            font-size: 0.875rem;
            margin-bottom: 8px;
        }

        .event-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .event-description {
            color: #4B5563;
            font-size: 0.9375rem;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }

        .event-location {
            display: flex;
            align-items: center;
            color: #6B7280;
            font-size: 0.875rem;
            margin-top: auto;
        }

        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            grid-column: 1 / -1;
        }

        .empty-state-icon {
            margin: 0 auto 16px;
            background: #F3F4F6;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .empty-state-description {
            color: #6B7280;
            margin-bottom: 16px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .view-all-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            background: #FFD24C;
            color: #825700;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .view-all-btn:hover {
            background: #FEC006;
            transform: translateY(-1px);
        }

        .section-header {
            margin-bottom: 32px;
        }

        .section-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            position: relative;
            display: inline-block;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 50%;
            height: 3px;
            background: #FFD24C;
            border-radius: 3px;
        }

        .section-description {
            color: #6B7280;
            font-size: 1.0625rem;
            max-width: 600px;
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 1.5rem;
            }

            .section-description {
                font-size: 1rem;
            }

            .event-card {
                max-width: 400px;
                margin-left: auto;
                margin-right: auto;
            }
        }
    </style>
@endpush

<section id="upcoming-events" class="pb-16">
    <div class="container mx-auto max-w-7xl">
        <div class="section-header">
            <h2 class="section-title">Kegiatan Mendatang</h2>
            <p class="section-description">
                Jadwal kegiatan dan acara penting di Kapel St. Yohanes Rasul
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($events as $recurringEvent)
                <a wire:click="$dispatch('showEventDetails', { eventId: {{ $recurringEvent->id }} })"
                    class="event-card group cursor-pointer">
                    <span class="event-badge">{{ $recurringEvent->event->eventCategory->name }}</span>

                    <div class="event-image-container">
                        @if ($recurringEvent->event->image)
                            <img src="{{ Storage::url($recurringEvent->event->image) }}"
                                alt="{{ $recurringEvent->event->name }}" class="event-image">
                        @else
                            <div
                                class="bg-gradient-to-br from-[#FFD24C] to-[#825700] h-full w-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div class="event-content">
                        <div class="event-date">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $recurringEvent->date->translatedFormat('l, d F Y') }} •
                            {{ $recurringEvent->time_start->format('H:i') }} WIB
                        </div>

                        <h3 class="event-title">{{ $recurringEvent->event->name }}</h3>

                        <p class="event-description">
                            {{ Str::limit($recurringEvent->event->description, 120) }}
                        </p>

                        <div class="event-location">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            @foreach ($recurringEvent->event->locations as $location)
                                {{ $location->name }}@if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </div>
                    </div>
                </a>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="empty-state-title">Tidak Ada Acara Mendatang</h3>
                        <p class="empty-state-description">
                            Saat ini tidak ada acara yang dijadwalkan. Silakan periksa kembali nanti untuk update terbaru.
                        </p>
                        <a href="{{ route('events.index') }}" class="view-all-btn">
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
