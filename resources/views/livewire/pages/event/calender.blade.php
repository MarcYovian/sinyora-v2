<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    {{-- HEADER KALENDER --}}
    <div class="p-4 sm:p-6 border-b">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Judul dan Navigasi Tanggal --}}
            <div class="flex items-center justify-between sm:justify-start gap-2">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 leading-tight whitespace-nowrap">
                    @if ($viewMode === 'month')
                        {{ $this->currentDate->locale('id')->monthName }} {{ $this->currentDate->year }}
                    @elseif ($viewMode === 'week')
                        <span class="hidden sm:inline">Minggu ke-</span>{{ $this->currentDate->weekOfYear }}
                    @else
                        {{ $this->currentDate->locale('id')->translatedFormat('j F Y') }}
                    @endif
                </h2>

                {{-- Tombol Navigasi --}}
                <div class="flex items-center">
                    <button wire:click="previous"
                        class="p-2 rounded-full text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FEC006]">
                        <x-heroicon-s-chevron-left class="h-5 w-5" />
                    </button>
                    <button wire:click="next"
                        class="p-2 rounded-full text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FEC006]">
                        <x-heroicon-s-chevron-right class="h-5 w-5" />
                    </button>
                </div>
            </div>

            {{-- Kontrol Sisi Kanan: Hari Ini & Pilihan Tampilan --}}
            <div class="flex items-center justify-between sm:justify-end space-x-2 sm:space-x-4">
                <button wire:click="goToToday"
                    class="px-3 py-1.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md whitespace-nowrap">
                    Hari Ini
                </button>

                {{-- Pilihan Tampilan Desktop --}}
                <div class="hidden sm:flex items-center rounded-md bg-gray-200 p-0.5">
                    <button wire:click="setViewMode('month')"
                        class="{{ $viewMode === 'month' ? 'bg-[#FFD24C] text-[#825700] shadow-sm' : 'text-gray-600 hover:bg-gray-300' }} px-3 py-1 rounded-md text-sm font-medium transition-colors">Bulan</button>
                    <button wire:click="setViewMode('week')"
                        class="{{ $viewMode === 'week' ? 'bg-[#FFD24C] text-[#825700] shadow-sm' : 'text-gray-600 hover:bg-gray-300' }} px-3 py-1 rounded-md text-sm font-medium transition-colors">Minggu</button>
                    <button wire:click="setViewMode('day')"
                        class="{{ $viewMode === 'day' ? 'bg-[#FFD24C] text-[#825700] shadow-sm' : 'text-gray-600 hover:bg-gray-300' }} px-3 py-1 rounded-md text-sm font-medium transition-colors">Hari</button>
                </div>

                {{-- Pilihan Tampilan Mobile (Dropdown) --}}
                <div class="sm:hidden relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center px-3 py-1.5 bg-gray-200 rounded-md text-sm font-medium text-gray-700">
                        <span>{{ ucfirst($viewMode) }}</span>
                        <x-heroicon-s-chevron-down class="h-4 w-4 ml-1" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-32 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-20"
                        x-cloak>
                        <div class="py-1">
                            <a href="#" wire:click.prevent="setViewMode('month')"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Bulan</a>
                            <a href="#" wire:click.prevent="setViewMode('week')"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Minggu</a>
                            <a href="#" wire:click.prevent="setViewMode('day')"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Hari</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Render Komponen Anak Sesuai Tampilan --}}
    <div class="p-2 sm:p-4">
        {{-- Indikator Loading --}}
        <div wire:loading.flex class="items-center justify-center py-16">
            <div class="flex items-center gap-2 text-gray-500">
                <x-heroicon-s-arrow-path class="h-6 w-6 animate-spin" />
                <span class="text-lg">Memuat data...</span>
            </div>
        </div>

        <div wire:loading.remove>
            @if ($viewMode === 'month')
                <livewire:pages.event.calender.month-view :year="$this->currentDate->year" :month="$this->currentDate->month"
                    wire:key="month-{{ $this->currentDate->format('Y-m') }}" />
            @elseif ($viewMode === 'week')
                <livewire:pages.event.calender.week-view :start-of-week="$this->currentDate->startOfWeek()"
                    wire:key="week-{{ $this->currentDate->format('Y-W') }}" />
            @elseif ($viewMode === 'day')
                <livewire:pages.event.calender.day-view :date="$this->currentDate"
                    wire:key="day-{{ $this->currentDate->format('Y-m-d') }}" />
            @endif
        </div>
    </div>
</div>
