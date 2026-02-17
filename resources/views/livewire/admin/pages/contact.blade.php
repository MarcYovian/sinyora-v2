<div class="flex flex-col md:flex-row h-[calc(100vh-6rem)] bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Left Panel: List -->
    <div class="w-full md:w-1/3 lg:w-1/4 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700 flex flex-col {{ $selectedContact ? 'hidden md:flex' : 'flex' }}">
        <!-- Header & Filters -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <!-- Inbox Title & Count -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Inbox</h2>
                <span
                    class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-indigo-900 dark:text-indigo-300">
                    {{ $contacts->total() }}
                </span>
            </div>

            <!-- Tabs -->
            <div class="flex space-x-1 bg-gray-100 dark:bg-gray-700 p-1 rounded-lg mb-4">
                <button wire:click="setStatusFilter('')"
                    class="flex-1 py-1 text-sm rounded-md transition-colors {{ $statusFilter == '' ? 'bg-white dark:bg-gray-600 shadow text-gray-900 dark:text-white font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                    All
                </button>
                <button wire:click="setStatusFilter('new')"
                    class="flex-1 py-1 text-sm rounded-md transition-colors {{ $statusFilter == 'new' ? 'bg-white dark:bg-gray-600 shadow text-gray-900 dark:text-white font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                    Unread
                </button>
            </div>

            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..."
                    class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-indigo-500 dark:focus:border-indigo-500">
            </div>
        </div>

        <!-- Scrollable List -->
        <div class="flex-1 overflow-y-auto">
            @forelse($contacts as $contact)
                <div wire:click="selectContact({{ $contact->id }})"
                    class="p-4 border-b border-gray-100 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $selectedContact && $selectedContact->id == $contact->id ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-l-indigo-500' : 'border-l-4 border-l-transparent' }}">
                    
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-semibold text-gray-900 dark:text-gray-100 truncate w-2/3">
                            {{ $contact->name }}
                        </span>
                        <span class="text-xs text-gray-500 whitespace-nowrap">
                            {{ $contact->created_at->format('M d') }}
                        </span>
                    </div>
                    
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1 line-clamp-1">
                        {{ $contact->email }}
                    </div>
                    
                    <div class="flex justify-between items-end">
                        <div class="text-xs text-gray-500 dark:text-gray-500 line-clamp-2 w-11/12">
                            {{ $contact->message }}
                        </div>
                        @if ($contact->status == 'new')
                            <span class="inline-block w-2 H-2 bg-indigo-600 rounded-full shrink-0 ml-1" title="New Message"></span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <p>No messages found.</p>
                </div>
            @endforelse
            
            <!-- Pagination inside the list -->
            <div class="p-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                 {{ $contacts->links(data: ['scrollTo' => false]) }}
            </div>
        </div>
    </div>

    <!-- Right Panel: Detail -->
    <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 {{ $selectedContact ? 'flex' : 'hidden md:flex' }}">
        @if ($selectedContact)
            <!-- Mobile Back Button & Toolbar -->
            <div class="h-16 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 sm:px-6 shrink-0">
                <div class="flex items-center gap-2">
                    <button wire:click="$set('selectedContact', null)" class="md:hidden mr-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <x-heroicon-o-arrow-left class="w-5 h-5" />
                    </button>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Message Details</h3>
                </div>
                
                <div class="flex space-x-2">
                     @if ($selectedContact->status !== 'replied')
                        <button wire:click="updateStatus({{ $selectedContact->id }}, 'replied')" 
                            class="text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors" title="Mark as Replied">
                            <x-heroicon-o-check-circle class="w-6 h-6" />
                        </button>
                    @endif
                    <button wire:click="confirmDelete({{ $selectedContact->id }})"
                        class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors" title="Delete">
                        <x-heroicon-o-trash class="w-6 h-6" />
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 pb-6 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-start sm:items-center space-x-4">
                        <div
                            class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-lg shrink-0">
                            {{ substr($selectedContact->name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $selectedContact->name }}</h4>
                            <div class="text-sm text-gray-500 dark:text-gray-400 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                <a href="mailto:{{ $selectedContact->email }}" class="text-indigo-600 hover:underline truncate">{{ $selectedContact->email }}</a>
                                <span class="hidden sm:inline">&bull;</span>
                                <span class="truncate">{{ $selectedContact->phone ?? 'No phone' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-0 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 px-3 py-1 rounded-full whitespace-nowrap self-start sm:self-auto">
                        {{ $selectedContact->created_at->format('D, M d Y, H:i') }}
                    </div>
                </div>

                <!-- Body -->
                <div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-200">
                    <p class="whitespace-pre-wrap leading-relaxed">{{ $selectedContact->message }}</p>
                </div>
            </div>

            <!-- Reply Area -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 shrink-0">
                <a href="mailto:{{ $selectedContact->email }}?subject=Re: Response to your message"
                    class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <x-heroicon-s-envelope class="w-4 h-4 mr-2" /> 
                    Reply via Email
                </a>
            </div>
        @else
            <!-- Empty State -->
            <div class="flex-1 flex items-center justify-center flex-col text-gray-400 dark:text-gray-500 p-8">
                <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-6 mb-4">
                    <x-heroicon-o-inbox class="w-12 h-12 text-gray-300 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">No Message Selected</h3>
                <p class="text-sm">Select a conversation from the list to start reading</p>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-modal name="delete-contact-confirmation" focusable>
        <form wire:submit="delete" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Hapus Pesan Kontak?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Apakah Anda yakin ingin menghapus pesan ini? Tindakan ini tidak dapat dibatalkan.') }}
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>
                <x-danger-button type="submit">
                    {{ __('Hapus') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
