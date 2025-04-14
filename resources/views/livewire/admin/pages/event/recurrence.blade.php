<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Event Occurrences:
                    {{ $event->name }}</h1>
                <div class="flex items-center gap-2 mt-2">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $event->status->color() }}">
                        {{ $event->status->label() }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $event->eventRecurrences->count() }} occurrences
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <x-button variant="secondary" :href="route('admin.event.show', $event)">
                    Back to Event
                </x-button>
            </div>
        </div>
        <!-- Main Content Grid -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">All Occurrences</h2>
                <div class="flex items-center gap-2">
                    <x-input-label for="search" class="sr-only" value="Search" />
                    <x-text-input wire:model.live.debounce.300ms="search" id="search" type="text"
                        placeholder="Search occurrences..." />
                </div>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Table Header -->
                <div
                    class="hidden md:grid grid-cols-12 gap-4 px-6 py-3 bg-gray-50 dark:bg-gray-700 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-600">
                    <div class="col-span-3">Date</div>
                    <div class="col-span-2">Day</div>
                    <div class="col-span-2">Start Time</div>
                    <div class="col-span-2">End Time</div>
                    <div class="col-span-3">Actions</div>
                </div>

                @forelse($occurrences as $occurrence)
                    <div
                        class="grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-4 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <!-- Date -->
                        <div class="md:col-span-3">
                            <div class="md:hidden text-xs font-medium text-gray-500 dark:text-gray-400">Date</div>
                            <input type="date" wire:model="occurrences.{{ $loop->index }}.date"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 rounded px-2 py-1">
                        </div>

                        <!-- Day -->
                        <div class="md:col-span-2">
                            <div class="md:hidden text-xs font-medium text-gray-500 dark:text-gray-400">Day</div>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ Carbon\Carbon::parse($occurrence['date'])->translatedFormat('l') }}
                            </div>
                        </div>

                        <!-- Start Time -->
                        <div class="md:col-span-2">
                            <div class="md:hidden text-xs font-medium text-gray-500 dark:text-gray-400">Start Time</div>
                            <input type="time" wire:model="occurrences.{{ $loop->index }}.time_start"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 rounded px-2 py-1">
                        </div>

                        <!-- End Time -->
                        <div class="md:col-span-2">
                            <div class="md:hidden text-xs font-medium text-gray-500 dark:text-gray-400">End Time</div>
                            <input type="time" wire:model="occurrences.{{ $loop->index }}.time_end"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 rounded px-2 py-1">
                        </div>

                        <!-- Actions -->
                        <div class="md:col-span-3 flex items-center justify-end gap-2">
                            <button wire:click="saveOccurrence({{ $loop->index }})"
                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 transition">
                                <x-heroicon-s-check class="h-5 w-5" />
                            </button>
                            <button wire:click="deleteOccurrence({{ $occurrence['id'] }})"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition">
                                <x-heroicon-s-trash class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <x-heroicon-s-calendar class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No occurrences found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add new occurrences to get started.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
