@props(['interval' => 30])

@php
    $model = $attributes->wire('model')->value();
@endphp

<div x-data="{
    open: false,
    time: $wire.entangle('{{ $model }}').defer,
    times: [],
    interval: {{ $interval }},
    init() {
        // Generate time slots
        for (let h = 0; h < 24; h++) {
            for (let m = 0; m < 60; m += this.interval) {
                const hours = h.toString().padStart(2, '0');
                const mins = m.toString().padStart(2, '0');
                this.times.push(`${hours}:${mins}`);
            }
        }

        // Set initial value from Livewire if exists
        if (this.time) {
            // Validate time format
            if (!this.time.includes(':')) {
                this.time = '08:00';
            }
        } else {
            this.time = this.times[16]; // Default to 08:00
        }
    },
    selectTime(t) {
        this.time = t;
        this.open = false;
        // Manually trigger Livewire update
        this.$wire.set('{{ $model }}', t);
    }
}" x-init="init()" class="relative">

    <x-text-input x-on:click="open = !open" x-ref="input" x-model="time"
        {{ $attributes->merge(['class' => 'cursor-pointer']) }} readonly wire:ignore />

    <div x-show="open" x-on:click.outside="open = false" x-transition
        class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md py-1 max-h-60 overflow-auto border border-gray-300 dark:border-gray-700">
        <template x-for="t in times" key="t">
            <button type="button" x-on:click="selectTime(t)"
                class="block w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900': time === t }">
                <span x-text="t"></span>
            </button>
        </template>
    </div>

    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <x-heroicon-s-clock class="h-5 w-5 text-gray-400 dark:text-gray-500" />
    </div>
</div>
