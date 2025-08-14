@props([
    'label' => 'Unknown',
    'colorClasses' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
])

{{--
    $attributes->merge() adalah best practice untuk memungkinkan penambahan class
    dari luar komponen jika suatu saat diperlukan.
--}}
<span {{ $attributes->merge(['class' => 'text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm ' . $colorClasses]) }}>
    {{ $label }}
</span>
