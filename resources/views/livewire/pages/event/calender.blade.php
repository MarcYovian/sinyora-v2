<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    {{-- HEADER KALENDER --}}
    <div class="flex items-center justify-between p-4 sm:p-6 border-b">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">
            {{-- Judul dinamis berdasarkan tampilan --}}
            @if ($viewMode === 'month')
                {{ $this->currentDate->locale('id')->monthName }} {{ $this->currentDate->year }}
            @elseif ($viewMode === 'week')
                Minggu ke-{{ $this->currentDate->weekOfYear }}, {{ $this->currentDate->year }}
            @else
                {{ $this->currentDate->locale('id')->translatedFormat('l, j F Y') }}
            @endif
        </h2>
        <div class="flex items-center space-x-1 sm:space-x-2">
            {{-- Tombol Navigasi --}}
            <button wire:click="previous" class="p-2 rounded-full text-gray-500 hover:bg-gray-100">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button wire:click="goToToday"
                class="px-3 py-1.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                Hari Ini
            </button>
            <button wire:click="next" class="p-2 rounded-full text-gray-500 hover:bg-gray-100">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            {{-- Pilihan Tampilan --}}
            <div class="ml-4 border-l pl-4">
                <button wire:click="setViewMode('month')"
                    class="{{ $viewMode === 'month' ? 'bg-[#FFD24C] text-white' : 'bg-gray-200' }} px-3 py-1 rounded-l-md text-sm">Bulan</button>
                <button wire:click="setViewMode('week')"
                    class="{{ $viewMode === 'week' ? 'bg-[#FFD24C] text-white' : 'bg-gray-200' }} px-3 py-1 text-sm">Minggu</button>
                <button wire:click="setViewMode('day')"
                    class="{{ $viewMode === 'day' ? 'bg-[#FFD24C] text-white' : 'bg-gray-200' }} px-3 py-1 rounded-r-md text-sm">Hari</button>
            </div>
        </div>
    </div>

    {{-- Render Komponen Anak Sesuai Tampilan --}}
    <div class="p-2 sm:p-4">
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
