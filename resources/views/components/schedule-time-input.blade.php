@props(['eventIndex', 'scheduleIndex', 'scheduleData', 'field', 'label'])

@php
    $processedData = $scheduleData[$field];
    $modelPath = "data.events.{$eventIndex}.schedule.{$scheduleIndex}.{$field}.time";
@endphp

<div>
    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
        {{ $label }}
    </label>

    @if ($processedData['status'] === 'error')
        <input type="time" wire:model.defer="{{ $modelPath }}"
            class="w-full px-3 py-2 border border-red-500 text-sm rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
            title="{{ $label }} (Perbaiki)">
        <p class="text-xs text-red-600 dark:text-red-400 mt-1">
            {{ $processedData['messages'] }}
        </p>
    @else
        <div
            class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-sm rounded-md shadow-sm text-gray-900 dark:text-gray-100 cursor-not-allowed">
            {{ $processedData['time'] ?: '--' }}
        </div>
    @endif
</div>
