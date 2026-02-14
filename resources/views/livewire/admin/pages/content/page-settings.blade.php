<div class="p-4 sm:p-6 lg:p-8" x-data="{ isDirty: false }" @input="isDirty = true"
    x-on:notify.window="if ($event.detail.title === 'Success') isDirty = false">
    
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            Manage Content
        </h1>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
             <span x-show="isDirty" x-transition class="text-sm text-yellow-600 dark:text-yellow-400 hidden sm:inline">
                Unsaved changes
            </span>
            <button wire:click="save" wire:loading.attr="disabled" wire:target="save,uploads"
                :disabled="!isDirty"
                :class="{
                    'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500': !isDirty,
                    'bg-green-600 hover:bg-green-700 focus:ring-green-500 animate-pulse': isDirty
                }"
                class="px-4 py-2 text-white rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-opacity-75 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="save,uploads">
                    <span x-show="!isDirty">Save Changes</span>
                    <span x-show="isDirty">Save All Changes</span>
                </span>
                <span wire:loading wire:target="save,uploads">
                    Saving...
                </span>
            </button>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar Navigation -->
        <div class="w-full lg:w-64 flex-shrink-0">
            <nav class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Pages
                    </h2>
                </div>
                <ul class="flex flex-col">
                    @foreach($availablePages as $key => $label)
                        <li>
                            <a href="{{ route('admin.content.index', ['page' => $key]) }}" 
                               wire:navigate
                               class="flex items-center px-4 py-3 text-sm font-medium transition-colors duration-200 {{ $page === $key ? 'bg-blue-50 text-blue-700 border-l-4 border-blue-600 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700 border-l-4 border-transparent' }}">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        <!-- Main Content Form -->
        <div class="flex-1">
            <form wire:submit.prevent="save" class="space-y-6">
                @if(empty($content))
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No content found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There are no settings available for this page yet.</p>
                    </div>
                @else
                    @foreach ($content as $sectionName => $fields)
                        <div x-data="{ expanded: true }"
                            class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 transition-all">
                            <div @click="expanded = !expanded"
                                class="p-4 sm:p-6 cursor-pointer flex justify-between items-center border-b border-gray-100 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 capitalize flex items-center">
                                    <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 py-1 px-2 rounded text-xs mr-3 uppercase">Section</span>
                                    {{ str_replace('-', ' ', $sectionName) }}
                                </h2>
                                <svg :class="{ 'rotate-180': expanded }"
                                    class="w-5 h-5 text-gray-500 transform transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                    </path>
                                </svg>
                            </div>

                            <div x-show="expanded" x-collapse class="px-4 sm:px-6 py-6">
                                <div class="grid gap-6">
                                    @foreach ($fields as $key => $field)
                                        <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-100 dark:border-gray-700">
                                            <label for="{{ $sectionName . '.' . $key }}"
                                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 capitalize">
                                                {{ str_replace('-', ' ', $key) }}
                                            </label>

                                            @switch($field['type'])
                                                @case('textarea')
                                                    <textarea id="{{ $sectionName . '.' . $key }}" wire:model.defer="content.{{ $sectionName }}.{{ $key }}.value"
                                                        rows="4"
                                                        class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-900 dark:text-gray-100"></textarea>
                                                @break

                                                @case('image')
                                                    <div class="flex flex-col sm:flex-row items-start space-y-4 sm:space-y-0 sm:space-x-6">
                                                        <div class="shrink-0">
                                                            @if (isset($uploads[$sectionName][$key]))
                                                                <img src="{{ $uploads[$sectionName][$key]->temporaryUrl() }}"
                                                                    class="w-32 h-32 object-cover rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
                                                            @elseif ($field['value'])
                                                                @php
                                                                    $decoded = json_decode($field['value'], true);
                                                                    $previewSrc = is_array($decoded) 
                                                                        ? asset($decoded['desktop'] ?? $decoded['tablet'] ?? reset($decoded))
                                                                        : asset($field['value']);
                                                                @endphp
                                                                <img src="{{ $previewSrc }}"
                                                                    class="w-32 h-32 object-cover rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
                                                                @if(is_array($decoded))
                                                                    <p class="mt-1 text-xs text-green-600 dark:text-green-400 flex items-center">
                                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                                        3 responsive variants
                                                                    </p>
                                                                @endif
                                                            @else
                                                                <div
                                                                    class="w-32 h-32 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center border border-gray-300 dark:border-gray-600 border-dashed">
                                                                    <svg class="w-8 h-8 text-gray-400" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1.586-1.586a2 2 0 00-2.828 0L6 14">
                                                                        </path>
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="flex-grow w-full">
                                                            <div class="flex items-center justify-center w-full">
                                                                <label for="{{ $sectionName . '.' . $key }}" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                                                        </svg>
                                                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WEBP (MAX. 2MB)</p>
                                                                    </div>
                                                                    <input type="file" id="{{ $sectionName . '.' . $key }}"
                                                                           wire:model="uploads.{{ $sectionName }}.{{ $key }}"
                                                                           class="hidden" accept="image/*">
                                                                </label>
                                                            </div> 

                                                            <!-- Loading & Error States -->
                                                            <div class="mt-2">
                                                                <div wire:loading wire:target="uploads.{{ $sectionName }}.{{ $key }}" class="text-sm text-blue-500 animate-pulse">
                                                                    Uploading image...
                                                                </div>
                                                                @error('uploads.' . $sectionName . '.' . $key)
                                                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                @break

                                                @default
                                                    <input type="text" id="{{ $sectionName . '.' . $key }}"
                                                        wire:model.defer="content.{{ $sectionName }}.{{ $key }}.value"
                                                        class="block w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-900 dark:text-gray-100">
                                            @endswitch
                                            
                                            <!-- Field description/hint if any -->
                                            @if ($sectionName === 'hero' && $key === 'title')
                                                 <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    Main title displayed for this page.
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </form>
        </div>
    </div>
</div>
