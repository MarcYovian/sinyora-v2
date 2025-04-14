@props([
    'variant' => 'primary',
    'size' => 'md',
    'pill' => false,
])

@php
    $classes = [
        'inline-flex items-center font-medium',
        'rounded' => !$pill,
        'rounded-full' => $pill,
        'px-2.5 py-0.5 text-xs' => $size === 'sm',
        'px-3 py-1 text-sm' => $size === 'md',
        'px-4 py-1.5 text-base' => $size === 'lg',
        'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300' => $variant === 'primary',
        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $variant === 'success',
        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $variant === 'danger',
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' => $variant === 'warning',
        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $variant === 'secondary',
    ];
@endphp

<span {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    {{ $slot }}
</span>
