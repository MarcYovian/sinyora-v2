<div x-data="{
    open: @entangle('showDropdown'),
    search: @entangle('search').live
}" x-on:click.away="open = false" class="relative">
    {{-- Container untuk input dan "pills" --}}
    <div
        class="form-input flex flex-wrap items-center gap-2 p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
        {{-- Pills untuk item yang sudah dipilih --}}
        @foreach ($selected as $item)
            <span
                class="flex items-center gap-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-medium px-2.5 py-0.5 rounded-full">
                {{ $item[$displayColumn] }}
                <button type="button" wire:click="removeItem('{{ $item['id'] }}')"
                    class="text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100">
                    &times;
                </button>
            </span>
        @endforeach

        {{-- Input untuk mencari atau menambah data baru --}}
        <input type="text" wire:model.live.debounce.300ms="search" x-on:focus="open = true"
            x-on:keydown.escape.prevent="open = false; $wire.resetSearch()"
            x-on:keydown.enter.prevent="$wire.addNewTag()" placeholder="Cari atau buat baru..."
            class="flex-grow p-0 border-none focus:ring-0 bg-transparent text-gray-900 dark:text-gray-200 dark:placeholder-gray-400">
    </div>

    {{-- Dropdown untuk hasil pencarian --}}
    <div x-show="open" x-transition
        class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg">
        <ul class="max-h-60 overflow-auto">
            @if ($options->isNotEmpty())
                @foreach ($options as $option)
                    {{-- Pastikan opsi belum dipilih --}}
                    @if (!in_array($option->id, array_column($selected, 'id')))
                        <li wire:click="selectItem({{ $option->id }})"
                            class="px-4 py-2 cursor-pointer text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            {{ $option->{$displayColumn} }}
                        </li>
                    @endif
                @endforeach
            @endif

            {{-- Opsi untuk membuat data baru --}}
            @if (!empty($search))
                @php
                    $isExisting =
                        $options->contains($displayColumn, $search) ||
                        collect($selected)->contains($displayColumn, $search);
                @endphp
                @unless ($isExisting)
                    <li wire:click="addNewTag()"
                        class="px-4 py-2 cursor-pointer text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        Buat baru: <span class="font-semibold">"{{ $search }}"</span>
                    </li>
                @endunless
            @endif

            @if ($options->isEmpty() && empty($search))
                <li class="px-4 py-2 text-gray-500 dark:text-gray-400">Ketik untuk mencari...</li>
            @endif
        </ul>
    </div>
</div>
