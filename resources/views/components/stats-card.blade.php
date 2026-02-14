@props(['title', 'count', 'color'])

@php
    $colorMap = [
        'blue' => [
            'border' => 'border-blue-500',
            'bg' => 'bg-blue-100 dark:bg-blue-900',
            'text' => 'text-blue-600 dark:text-blue-300',
        ],
        'yellow' => [
            'border' => 'border-yellow-500',
            'bg' => 'bg-yellow-100 dark:bg-yellow-900',
            'text' => 'text-yellow-600 dark:text-yellow-300',
        ],
        'green' => [
            'border' => 'border-green-500',
            'bg' => 'bg-green-100 dark:bg-green-900',
            'text' => 'text-green-600 dark:text-green-300',
        ],
        'purple' => [
            'border' => 'border-purple-500',
            'bg' => 'bg-purple-100 dark:bg-purple-900',
            'text' => 'text-purple-600 dark:text-purple-300',
        ],
        'indigo' => [
            'border' => 'border-indigo-500',
            'bg' => 'bg-indigo-100 dark:bg-indigo-900',
            'text' => 'text-indigo-600 dark:text-indigo-300',
        ],
        'orange' => [
            'border' => 'border-orange-500',
            'bg' => 'bg-orange-100 dark:bg-orange-900',
            'text' => 'text-orange-600 dark:text-orange-300',
        ],
        'teal' => [
            'border' => 'border-teal-500',
            'bg' => 'bg-teal-100 dark:bg-teal-900',
            'text' => 'text-teal-600 dark:text-teal-300',
        ],
        'pink' => [
            'border' => 'border-pink-500',
            'bg' => 'bg-pink-100 dark:bg-pink-900',
            'text' => 'text-pink-600 dark:text-pink-300',
        ],
    ];

    $colors = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border-l-4 {{ $colors['border'] }}">
    <div class="flex items-center">
        <div class="flex-shrink-0 {{ $colors['bg'] }} rounded-lg p-3">
            <svg class="w-6 h-6 {{ $colors['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {{ $icon }}
            </svg>
        </div>
        <div class="ml-4">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white"
               x-data="{ displayed: 0, target: {{ $count }} }"
               x-init="
                   if (target === 0) { displayed = 0; return; }
                   const duration = 600;
                   const steps = 30;
                   const increment = target / steps;
                   let current = 0;
                   const interval = setInterval(() => {
                       current += increment;
                       if (current >= target) {
                           displayed = target;
                           clearInterval(interval);
                       } else {
                           displayed = Math.round(current);
                       }
                   }, duration / steps);
               "
               x-text="displayed.toLocaleString('id-ID')">
                {{ $count }}
            </p>
        </div>
    </div>
</div>
