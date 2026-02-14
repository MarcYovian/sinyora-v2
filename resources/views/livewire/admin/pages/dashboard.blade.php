<div>
    {{-- Page Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $greeting }}, {{ auth()->user()->name }}!
            </p>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500">
            <svg class="w-3.5 h-3.5 inline-block mr-0.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Diperbarui: {{ $lastUpdated }}
        </p>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        {{-- Events Card --}}
        @can('view events')
            <x-stats-card title="Total Events" :count="$totalEvents" color="blue">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </x-slot:icon>
            </x-stats-card>
        @endcan

        {{-- Pending Events Card --}}
        @can('approve event')
            <x-stats-card title="Events Pending" :count="$pendingEvents" color="yellow">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </x-slot:icon>
            </x-stats-card>
        @endcan

        {{-- Articles Card --}}
        @can('view articles')
            <x-stats-card title="Articles Published" :count="$totalArticles" color="green">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </x-slot:icon>
            </x-stats-card>
        @endcan

        {{-- Draft Articles Card --}}
        @can('create article')
            <x-stats-card title="Draft Articles" :count="$draftArticles" color="purple">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </x-slot:icon>
            </x-stats-card>
        @endcan

        {{-- Asset Borrowings Card --}}
        @can('view asset borrowings')
            <x-stats-card title="Total Borrowings" :count="$totalBorrowings" color="indigo">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </x-slot:icon>
            </x-stats-card>
        @endcan

        {{-- Pending Borrowings Card --}}
        @can('view asset borrowings')
            <x-stats-card title="Borrowings Pending" :count="$pendingBorrowings" color="orange">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </x-slot:icon>
            </x-stats-card>
        @endcan

        {{-- Documents Card --}}
        @can('view documents')
            <x-stats-card title="Documents Pending" :count="$pendingDocuments" color="teal">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </x-slot:icon>
            </x-stats-card>
        @endcan

        {{-- Users Card --}}
        @can('view users')
            <x-stats-card title="Total Users" :count="$totalUsers" color="pink">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </x-slot:icon>
            </x-stats-card>
        @endcan
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pending Events Approval --}}
        @can('approve event')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Events Menunggu Approval</h2>
                        <a href="{{ route('admin.events.index', ['status' => 'pending']) }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pendingEventsList as $event)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('admin.events.show', $event) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 truncate block">
                                        {{ $event->name }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $event->eventCategory?->name ?? 'No Category' }} •
                                        {{ $event->organization?->name ?? 'No Organization' }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $event->status->color() }}">
                                    {{ $event->status->label() }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada event yang menunggu approval</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endcan

        {{-- Pending Borrowings --}}
        @can('view asset borrowings')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Peminjaman Menunggu Approval</h2>
                        <a href="{{ route('admin.asset-borrowings.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pendingBorrowingsList as $borrowing)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $borrowing->borrower }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $borrowing->start_datetime->format('d M Y H:i') }} - {{ $borrowing->end_datetime->format('d M Y H:i') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $borrowing->assets_count }} item(s)
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $borrowing->status->color() }}">
                                    {{ $borrowing->status->label() }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada peminjaman yang menunggu approval</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endcan

        {{-- Upcoming Events --}}
        @can('view events')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Event Mendatang (7 Hari)</h2>
                        <a href="{{ route('admin.events.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($upcomingEvents as $recurrence)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-start gap-4">
                                {{-- Date Badge --}}
                                <div class="flex-shrink-0 text-center bg-blue-50 dark:bg-blue-900/30 rounded-lg p-2 min-w-[60px]">
                                    <p class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase">
                                        {{ $recurrence->date->translatedFormat('M') }}
                                    </p>
                                    <p class="text-xl font-bold text-blue-700 dark:text-blue-300">
                                        {{ $recurrence->date->format('d') }}
                                    </p>
                                </div>
                                {{-- Event Info --}}
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('admin.events.show', $recurrence->event) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 truncate block">
                                        {{ $recurrence->event->name }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <span class="inline-flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $recurrence->time_start->format('H:i') }} - {{ $recurrence->time_end->format('H:i') }}
                                        </span>
                                    </p>
                                    @if($recurrence->event->eventCategory)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 mt-1">
                                            {{ $recurrence->event->eventCategory->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada event dalam 7 hari ke depan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endcan

        {{-- Recent Articles --}}
        @can('view articles')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Artikel Terbaru</h2>
                        <a href="{{ route('admin.articles.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentArticles as $article)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-start gap-4">
                                {{-- Thumbnail --}}
                                @if($article->featured_image)
                                    <img src="{{ asset('storage/' . $article->featured_image) }}" alt="{{ $article->title }}" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                {{-- Article Info --}}
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('admin.articles.edit', $article->id) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 truncate block">
                                        {{ $article->title }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $article->user?->name ?? 'Unknown' }} • {{ $article->published_at?->diffForHumans() ?? 'Not published' }}
                                    </p>
                                    @if($article->category)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 mt-1">
                                            {{ $article->category->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada artikel yang dipublikasikan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endcan
    </div>

    {{-- Quick Actions --}}
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aksi Cepat</h2>
        <div class="flex flex-wrap gap-3">
            @can('create event')
                <a href="{{ route('admin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Buat Event
                </a>
            @endcan

            @can('create article')
                <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tulis Artikel
                </a>
            @endcan

            @can('create asset borrowing')
                <a href="{{ route('admin.asset-borrowings.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Ajukan Peminjaman
                </a>
            @endcan

            @can('view documents')
                <a href="{{ route('admin.documents.index') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Kelola Dokumen
                </a>
            @endcan

            @can('view users')
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    Kelola Users
                </a>
            @endcan
        </div>
    </div>

</div>
