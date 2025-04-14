@props(['placeholder' => 'Select date'])

@php
    $model = $attributes->wire('model')->value();
@endphp

<div x-data="{
    picker: null,
    currentValue: @entangle($model).defer,
    init() {
        this.picker = new Pikaday({
            field: this.$refs.input,
            format: 'YYYY-MM-DD',
            onSelect: () => {
                // Update Alpine data without triggering Livewire yet
                this.currentValue = this.picker.getDate().toISOString().split('T')[0];

                // Manually update Livewire after slight delay
                setTimeout(() => {
                    this.$wire.set('{{ $model }}', this.currentValue);
                }, 100);
            }
        });

        // Initialize with current value if exists
        if (this.currentValue) {
            this.picker.setDate(this.currentValue);
        }

        // Watch for external changes (from Livewire)
        this.$watch('currentValue', (value) => {
            if (value && this.picker.getDate() !== value) {
                this.picker.setDate(value);
            }
        });
    }
}" wire:ignore class="relative">

    <x-text-input x-ref="input" x-model="currentValue"
        {{ $attributes->merge([
            'type' => 'text',
            'class' => 'pl-10 w-full',
            'placeholder' => $placeholder,
        ]) }} />

    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
        <x-heroicon-s-calendar class="h-5 w-5 text-gray-400 dark:text-gray-500" />
    </div>
</div>
