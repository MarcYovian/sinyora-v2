<div class="p-4 sm:p-6 lg:p-8" x-data="{ isDirty: false }" @input="isDirty = true"
    x-on:notify.window="if ($event.detail.title === 'Success') isDirty = false">
    <div
        class="sticky top-0 z-10 bg-gray-50/75 dark:bg-gray-900/75 backdrop-blur-lg -mx-4 -mt-4 sm:-mx-6 sm:-mt-6 lg:-mx-8 lg:-mt-8 px-4 sm:px-6 lg:px-8 py-4 mb-6 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                Manage Home Content
            </h1>
            <div class="flex items-center space-x-3">
                <span x-show="isDirty" x-transition class="text-sm text-yellow-600 dark:text-yellow-400 hidden sm:inline">
                    Unsaved changes
                </span>

                <button wire:click="save" wire:loading.attr="disabled" wire:target="save,uploads" :disabled="!isDirty"
                    :class="{
                        'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500': !isDirty,
                        'bg-green-600 hover:bg-green-700 focus:ring-green-500 animate-pulse': isDirty
                    }"
                    class="px-4 py-2 text-white rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-opacity-75 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:animate-none">
                    <span wire:loading.remove wire:target="save,uploads">
                        <span x-show="!isDirty">Saved</span>
                        <span x-show="isDirty">Save Changes</span>
                    </span>
                    <span wire:loading wire:target="save,uploads">
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <form wire:submit.prevent="save" class="space-y-8">
            @foreach ($content as $sectionName => $fields)
                <div x-data="{ expanded: true }"
                    class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 transition-all">
                    <div @click="expanded = !expanded"
                        class="p-4 sm:p-6 cursor-pointer flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 capitalize">
                            {{ str_replace('-', ' ', $sectionName) }} Section
                        </h2>
                        <svg :class="{ 'rotate-180': expanded }"
                            class="w-5 h-5 text-gray-500 transform transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>

                    <div x-show="expanded" x-collapse class="px-4 sm:px-6 pb-6">
                        <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                            @foreach ($fields as $key => $field)
                                <div>
                                    <label for="{{ $sectionName . '.' . $key }}"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ Str::ucfirst(str_replace('-', ' ', $key)) }}
                                    </label>

                                    @switch($field['type'])
                                        @case('textarea')
                                            <textarea id="{{ $sectionName . '.' . $key }}" wire:model.defer="content.{{ $sectionName }}.{{ $key }}.value"
                                                rows="4"
                                                class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-900 dark:text-gray-100"></textarea>
                                        @break

                                        @case('image')
                                            <div class="flex items-start space-x-4">
                                                <div class="shrink-0">
                                                    @if (isset($uploads[$sectionName][$key]))
                                                        <img src="{{ $uploads[$sectionName][$key]->temporaryUrl() }}"
                                                            class="w-24 h-24 object-cover rounded-md shadow-sm">
                                                    @elseif ($field['value'])
                                                        <img src="{{ asset($field['value']) }}"
                                                            class="w-24 h-24 object-cover rounded-md shadow-sm">
                                                    @else
                                                        <div
                                                            class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-md flex items-center justify-center">
                                                            <svg class="w-10 h-10 text-gray-400" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1.586-1.586a2 2 0 00-2.828 0L6 14">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow">
                                                    <div wire:loading
                                                        wire:target="uploads.{{ $sectionName }}.{{ $key }}"
                                                        class="text-sm text-gray-500 mb-2">Uploading...</div>
                                                    <label for="{{ $sectionName . '.' . $key }}"
                                                        class="cursor-pointer inline-block px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                        <span>Change Image</span>
                                                        <input type="file" id="{{ $sectionName . '.' . $key }}"
                                                            wire:model="uploads.{{ $sectionName }}.{{ $key }}"
                                                            class="sr-only">
                                                    </label>
                                                    <p class="text-xs text-gray-500 mt-2">PNG, JPG, WEBP up to 2MB.</p>
                                                    @error('uploads.' . $sectionName . '.' . $key)
                                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        @break

                                        @default
                                            <input type="text" id="{{ $sectionName . '.' . $key }}"
                                                wire:model.defer="content.{{ $sectionName }}.{{ $key }}.value"
                                                class="block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-900 dark:text-gray-100">
                                    @endswitch

                                    @if ($sectionName === 'hero' && $key === 'title')
                                        <p class="mt-1 text-xs text-gray-500">This is the main headline on the homepage.
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </form>
    </div>
</div>
