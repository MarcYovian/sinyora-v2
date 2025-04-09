<x-sidebar.sidebar :showOnDesktop="true">
    @foreach ($menus as $key => $menu)
        @if ($menu->count() > 1)
            <x-sidebar.dropdown title="{{ $key }}" icon="{{ $menu->first()->icon }}" :active="false">
                @foreach ($menu as $item)
                    <x-sidebar.sub-link href="{{ route($item->route_name) }}"
                        wire:navigate>{{ $item->menu }}</x-sidebar.sub-link>
                @endforeach
            </x-sidebar.dropdown>
        @else
            <x-sidebar.item href="{{ route($menu->first()->route_name) }}" :active="false" wire:navigate>
                @svg('heroicon-' . $menu->first()->icon, 'w-5 h-5')
                <span class="ms-3">{{ $menu->first()->menu }}</span>
            </x-sidebar.item>
        @endif
    @endforeach
</x-sidebar.sidebar>
