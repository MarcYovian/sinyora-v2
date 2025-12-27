<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Tags') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            @can('create article tag')
                <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />

                    <span>{{ __('Create') }}</span>
                </x-button>
            @endcan

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search tags by name.." />
            </div>
        </div>

        {{-- =================================================================== --}}
        {{-- DAFTAR KONTEN (RESPONSIVE: CARD -> TABLE) --}}
        {{-- =================================================================== --}}
        <div class="relative overflow-x-auto">
            {{-- Tampilan Tabel untuk Tablet/Laptop (md and up) --}}
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 hidden md:table">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        @foreach ($table_heads as $head)
                            <th scope="col" class="px-6 py-3">{{ $head }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tags as $key => $tag)
                        <tr wire:key="tag-desktop-{{ $tag->id }}"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">{{ $key + $tags->firstItem() }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $tag->name }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    @can('edit article tag')
                                        <x-button size="sm" variant="warning" type="button" class="!p-2"
                                            wire:click="edit({{ $tag->id }})">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                            <span class="sr-only">Edit</span>
                                        </x-button>
                                    @endcan
                                    @can('delete article tag')
                                        <x-button size="sm" variant="danger" type="button" class="!p-2"
                                            wire:click="confirmDelete({{ $tag->id }})">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                            <span class="sr-only">Delete</span>
                                        </x-button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($table_heads) }}" class="text-center py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <x-heroicon-o-hashtag class="w-12 h-12 text-gray-400" />
                                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mt-2">{{ __('Belum Ada Tag') }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ __('Buat tag baru untuk memulai.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Tampilan Card untuk Mobile (default, hidden on md) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 md:hidden">
                @forelse ($tags as $key => $tag)
                    <div wire:key="tag-mobile-{{ $tag->id }}"
                        class="bg-white dark:bg-gray-900 rounded-lg shadow-md overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700">
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">#{{ $key + $tags->firstItem() }}</span>
                            </div>
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mt-2 text-lg">
                                {{ $tag->name }}
                            </h3>
                        </div>
                        <div class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700 mt-2 pt-4 flex items-center justify-end space-x-2">
                            @can('edit article tag')
                                <x-button type="button" variant="warning" size="sm"
                                    wire:click="edit({{ $tag->id }})">
                                    {{ __('Edit') }}
                                </x-button>
                            @endcan
                            @can('delete article tag')
                                <x-button type="button" variant="danger" size="sm"
                                    wire:click="confirmDelete({{ $tag->id }})">
                                    {{ __('Delete') }}
                                </x-button>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 sm:col-span-2 text-center py-8">
                        <div class="flex flex-col items-center justify-center">
                            <x-heroicon-o-hashtag class="w-12 h-12 text-gray-400" />
                            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mt-2">{{ __('Belum Ada Tag') }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ __('Buat tag baru untuk memulai.') }}</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="px-6 py-4">
            {{ $tags->links() }}
        </div>
    </div>

    <x-modal name="tag-modal" :show="$errors->isNotEmpty()" maxWidth="md" focusable>
        <form wire:submit="save" class="p-6">
            <!-- Header with close button -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ $editId ? __('Edit Tag') : __('Create New Tag') }}
                </h2>
                <button type="button" @click="$dispatch('close')"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <x-heroicon-s-x-circle class="h-6 w-6" />
                </button>
            </div>

            <div class="space-y-6">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" value="{{ __('Name') }}" class="mb-1" />
                    <x-text-input wire:model="form.name" id="name" name="name" type="text"
                        class="block w-full mt-1" placeholder="{{ __('e.g. Misa, Meeting') }}" />
                    <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')" class="w-full sm:w-auto justify-center">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit" class="w-full sm:w-auto justify-center">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update') : __('Create') }}
                    </span>
                    <span wire:loading wire:target="save">
                        {{ __('Saving...') }}
                    </span>
                    <x-heroicon-s-arrow-path wire:loading wire:target="save" class="ml-2 h-4 w-4 animate-spin" />
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-tag-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this tag?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this tag by clicking the button below.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
