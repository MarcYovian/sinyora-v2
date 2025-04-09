@props(['disabled' => false])

<input type="checkbox" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' =>
        'rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:checked:bg-indigo-600 dark:checked:border-indigo-600',
]) !!}>
