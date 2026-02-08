<div>
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('Manajemen Peminjaman Aset') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola semua permintaan peminjaman aset yang masuk.
        </p>
    </header>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="p-4 sm:p-6 space-y-4">
            {{-- Header Kontrol: Tombol, Filter, dan Pencarian --}}
            {{-- Top Actions Bar --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                @can('create asset borrowing')
                    <x-button type="button" variant="primary" href="{{ route('admin.asset-borrowings.create') }}"
                        class="items-center max-w-xs gap-2">
                        <x-heroicon-s-plus class="w-5 h-5" />
                        <span>{{ __('Buat Peminjaman') }}</span>
                    </x-button>
                @endcan

                <div class="flex-grow flex flex-col sm:flex-row items-center gap-3">
                    <div class="w-full sm:w-auto sm:flex-grow">
                        <x-text-input wire:model.live.debounce.300ms="search" type="text" class="w-full"
                            placeholder="{{ __('Cari nama peminjam/event...') }}" />
                    </div>
                    <div class="w-full sm:w-48">
                        <x-select wire:model.live="filterStatus" class="w-full">
                            <option value="">{{ __('Semua Status') }}</option>
                            @foreach (App\Enums\BorrowingStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    @if ($search || $filterStatus)
                        <x-button type="button" wire:click="resetFilters" variant="secondary" class="w-full sm:w-auto">
                            {{ __('Reset') }}
                        </x-button>
                    @endif
                </div>
            </div>

            {{-- Indikator Loading --}}
            <div wire:loading.flex wire:target="search, filterStatus" class="items-center justify-center w-full py-4">
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                    <span>Memuat data...</span>
                </div>
            </div>

            <div wire:loading.remove wire:target="search, filterStatus">
                {{-- Tampilan Card untuk Mobile (Mobile First) --}}
                <div class="grid grid-cols-1 gap-4 md:hidden">
                    @forelse ($borrowings as $borrowing)
                        <div wire:key="borrowing-card-{{ $borrowing->id }}"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden ring-1 ring-black ring-opacity-5">
                            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">
                                        {{ $borrowing->borrower }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $borrowing->created_at->diffForHumans() }}
                                    </p>
                                    <div class="mt-1">
                                         <x-status-badge :status="$borrowing->status" />
                                    </div>
                                </div>
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button
                                            class="p-1 text-gray-500 dark:text-gray-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                            <x-heroicon-s-ellipsis-vertical class="w-5 h-5" />
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        @can('view asset borrowing details')
                                             <x-dropdown-link wire:click="show({{ $borrowing->id }})">
                                                Detail
                                            </x-dropdown-link>
                                        @endcan
                                        @can('edit asset borrowing')
                                            <x-dropdown-link
                                                href="{{ route('admin.asset-borrowings.edit', $borrowing) }}">Edit</x-dropdown-link>
                                        @endcan
                                        @if ($borrowing->status === App\Enums\BorrowingStatus::PENDING)
                                            @can('approve borrowing asset')
                                                <x-dropdown-link
                                                    wire:click="confirmApprove({{ $borrowing->id }})">Approve</x-dropdown-link>
                                            @endcan
                                            @can('reject borowing asset')
                                                <x-dropdown-link
                                                    wire:click="confirmReject({{ $borrowing->id }})">Reject</x-dropdown-link>
                                            @endcan
                                        @endif
                                        <div class="border-t border-gray-100 dark:border-gray-600 my-1"></div>
                                        @can('delete asset borrowing')
                                            <x-dropdown-link wire:click="confirmDelete({{ $borrowing->id }})"
                                                class="text-red-600 dark:text-red-500">Delete
                                            </x-dropdown-link>
                                        @endcan
                                    </x-slot>
                                </x-dropdown>
                            </div>
                            <div class="p-4 space-y-3 text-sm">
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-calendar class="w-4 h-4 mr-2 flex-shrink-0" />
                                    <span>{{ $borrowing->start_datetime->format('d M Y, H:i') }} -
                                        {{ $borrowing->end_datetime->format('H:i') }}</span>
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-link class="w-4 h-4 mr-2 flex-shrink-0" />
                                    <span>{{ $borrowing->event?->name ?? 'Aktivitas Internal' }}</span>
                                </div>

                                {{-- Mobile Card Actions --}}
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-4">
                                     @can('view asset borrowing details')
                                        <button wire:click="show({{ $borrowing->id }})" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline flex items-center gap-1.5 transition-colors">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                            <span>{{ __('Detail') }}</span>
                                        </button>
                                    @endcan

                                    @if ($borrowing->status === App\Enums\BorrowingStatus::PENDING)
                                        @can('approve borrowing asset')
                                            <button wire:click="confirmApprove({{ $borrowing->id }})" class="text-green-600 dark:text-green-400 font-medium hover:underline flex items-center gap-1.5 transition-colors">
                                                <x-heroicon-o-check class="w-4 h-4" />
                                                <span>{{ __('Approve') }}</span>
                                            </button>
                                        @endcan
                                        @can('reject borowing asset')
                                            <button wire:click="confirmReject({{ $borrowing->id }})" class="text-red-500 dark:text-red-400 font-medium hover:underline flex items-center gap-1.5 transition-colors">
                                                <x-heroicon-o-x-mark class="w-4 h-4" />
                                                <span>{{ __('Reject') }}</span>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400 col-span-1">
                            <x-heroicon-o-inbox class="mx-auto h-12 w-12" />
                            <h4 class="mt-2 text-sm font-semibold">{{ __('Tidak ada data peminjaman') }}</h4>
                            <p class="mt-1 text-sm">{{ __('Coba ubah filter Anda atau buat peminjaman baru.') }}</p>
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Table untuk Desktop --}}
                <div class="hidden md:block">
                    <x-table title="Data Peminjaman Aset" :heads="$table_heads">
                        @forelse ($borrowings as $borrowing)
                            <tr wire:key="borrowing-row-{{ $borrowing->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $loop->iteration + $borrowings->firstItem() - 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $borrowing->borrower }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $borrowing->creator->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <div>{{ $borrowing->start_datetime->translatedFormat('d M Y, H:i') }}</div>
                                    <div class="text-xs">sampai</div>
                                    <div>{{ $borrowing->end_datetime->translatedFormat('d M Y, H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $borrowing->event?->name ?? 'Aktivitas Internal' }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-status-badge :status="$borrowing->status" />
                                </td>
                                <td class="px-6 py-4 text-right">
                                    {{-- Dropdown Aksi untuk Desktop --}}
                                    <div class="flex items-center justify-end space-x-1">
                                        @can('view asset borrowing details')
                                            <x-button type="button" variant="primary" size="sm" class="!p-2"
                                                wire:click="show({{ $borrowing->id }})" title="Detail">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span class="sr-only">Detail</span>
                                            </x-button>
                                        @endcan

                                        @can('edit asset borrowing')
                                            <x-button type="button" variant="warning" size="sm" class="!p-2"
                                                href="{{ route('admin.asset-borrowings.edit', $borrowing) }}" title="Edit">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                <span class="sr-only">Edit</span>
                                            </x-button>
                                        @endcan

                                        @if ($borrowing->status === App\Enums\BorrowingStatus::PENDING)
                                            @can('approve borrowing asset')
                                                <x-button type="button" variant="success" size="sm" class="!p-2"
                                                    wire:click="confirmApprove({{ $borrowing->id }})" title="Approve">
                                                    <x-heroicon-o-check class="w-4 h-4" />
                                                    <span class="sr-only">Approve</span>
                                                </x-button>
                                            @endcan
                                            @can('reject borowing asset')
                                                <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                    wire:click="confirmReject({{ $borrowing->id }})" title="Reject">
                                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                                    <span class="sr-only">Reject</span>
                                                </x-button>
                                            @endcan
                                        @endif

                                        @can('delete asset borrowing')
                                            <x-button type="button" variant="danger" size="sm" class="!p-2"
                                                wire:click="confirmDelete({{ $borrowing->id }})" title="Hapus">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                <span class="sr-only">Delete</span>
                                            </x-button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($table_heads) }}"
                                    class="text-center py-12 text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-inbox class="mx-auto h-12 w-12" />
                                    <h4 class="mt-2 text-sm font-semibold">{{ __('Tidak ada data peminjaman') }}</h4>
                                    <p class="mt-1 text-sm">
                                        {{ __('Coba ubah filter Anda atau buat peminjaman baru.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="px-4 md:px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $borrowings->links() }}
        </div>
    </div>

    <x-modal name="borrowing-detail-modal" maxWidth="5xl" focusable>
        @if ($this->borrowing)
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-start justify-between pb-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                            {{ __('Borrowing Request') }} #{{ $this->borrowing->id ?? 'N/A' }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('Detail lengkap permintaan peminjaman aset.') }}
                        </p>
                    </div>
                    <button type="button" @click="$dispatch('close')"
                        class="p-2 -m-2 text-gray-400 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-200 transition-all">
                        <x-heroicon-s-x-mark class="h-6 w-6" />
                    </button>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column - Borrowing Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Status Badge -->
                        <div class="flex items-center gap-4">
                            <span
                                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                              {{ $this->borrowing->status === App\Enums\BorrowingStatus::PENDING
                                  ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                  : ($this->borrowing->status === App\Enums\BorrowingStatus::APPROVED
                                      ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                      : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                {{ $this->borrowing->status->label() }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Submitted {{ $this->borrowing->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Borrower Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Borrower
                                    Information
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Borrower Name
                                        </p>
                                        <p class="mt-1 text-gray-900 dark:text-gray-100">
                                            {{ $this->borrowing->borrower }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number
                                        </p>
                                        <p class="mt-1 text-gray-900 dark:text-gray-100">
                                            {{ $this->borrowing->borrower_phone }}
                                        </p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</p>
                                        <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $this->borrowing->notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Borrowing Period -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Borrowing Period
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</p>
                                        <p class="mt-1 text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($this->borrowing->start_datetime)->format('M j, Y g:i A') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</p>
                                        <p class="mt-1 text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($this->borrowing->end_datetime)->format('M j, Y g:i A') }}
                                        </p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</p>
                                        <p class="mt-1 text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($this->borrowing->start_datetime)->diffForHumans(\Carbon\Carbon::parse($this->borrowing->end_datetime), true) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Borrowed Assets -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Borrowed Assets
                                    ({{ count($this->borrowing->assets) }})</h3>
                                <div class="space-y-4">
                                    @foreach ($this->borrowing->assets as $asset)
                                        <div class="border rounded-lg overflow-hidden dark:border-gray-700">
                                            <div class="flex">
                                                <!-- Asset Image -->
                                                <div class="flex-shrink-0 w-32 h-32 bg-gray-100 dark:bg-gray-700">
                                                    <img class="w-full h-full object-cover" src="{{ $asset->image }}"
                                                        alt="{{ $asset->name }}">
                                                </div>

                                                <!-- Asset Details -->
                                                <div class="flex-1 p-4">
                                                    <div class="flex justify-between">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $asset->name }}</h4>
                                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                                            Qty: {{ $asset->pivot->quantity }}
                                                        </span>
                                                    </div>
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $asset->description }}</p>
                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                            {{ $asset->code }}
                                                        </span>
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                            {{ $asset->storage_location }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Meta Information -->
                    <div class="space-y-6">
                        <!-- Requestor Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Requestor</h3>
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex-shrink-0 h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <span class="text-lg font-medium text-gray-600 dark:text-gray-300">
                                            {{ substr($this->borrowing->creator->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $this->borrowing->creator->name }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $this->borrowing->creator->email }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Event Information (if associated) -->
                        @if ($this->borrowing->event)
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Associated
                                        Event
                                    </h3>
                                    <div class="space-y-2">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $this->borrowing->event->name }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Actions</h3>
                                <div class="space-y-3">
                                    @if ($this->borrowing->status === App\Enums\BorrowingStatus::PENDING)
                                        @can('approve borrowing asset')
                                            <x-button variant="success" class="w-full"
                                                wire:click="confirmApprove({{ $this->borrowing->id }})">
                                                <x-heroicon-s-check class="h-5 w-5 mr-2" />
                                                Approve Request
                                            </x-button>
                                        @endcan
                                        @can('reject borowing asset')
                                            <x-button variant="danger" class="w-full"
                                                wire:click="confirmReject({{ $this->borrowing->id }})">
                                                <x-heroicon-s-x-mark class="h-5 w-5 mr-2" />
                                                Reject Request
                                            </x-button>
                                        @endcan
                                    @endif

                                    @can('edit asset borrowing')
                                        <x-button variant="secondary" class="w-full"
                                            href="{{ route('admin.asset-borrowings.edit', $this->borrowing) }}">
                                            <x-heroicon-s-pencil class="h-5 w-5 mr-2" />
                                            Edit Request
                                        </x-button>
                                    @endcan

                                    {{-- <x-button variant="secondary" class="w-full" onclick="window.print()">
                                        <x-heroicon-s-printer class="h-5 w-5 mr-2" />
                                        Print Details
                                    </x-button> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-modal>

    <x-modal name="approve-borrowing-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="approve" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Konfirmasi Persetujuan') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Apakah Anda yakin ingin menyetujui peminjaman ini? Aset akan direservasi untuk peminjam.') }}
            </p>

             @if ($this->borrowing)
                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $this->borrowing->borrower }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Request #{{ $this->borrowing->id }} &bull; {{ $this->borrowing->created_at->diffForHumans() }}
                    </div>
                </div>
            @endif

            <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                    <span class="text-sm font-medium text-green-900 dark:text-green-200">
                        {{ __('Status akan berubah menjadi Approved') }}
                    </span>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-button variant="success">
                    <span wire:loading.remove wire:target="approve">{{ __('Setujui Peminjaman') }}</span>
                    <span wire:loading wire:target="approve" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        {{ __('Memproses...') }}
                    </span>
                </x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="reject-borrowing-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="reject" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Konfirmasi Penolakan') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Apakah Anda yakin ingin menolak permohonan peminjaman ini?') }}
            </p>

            @if ($this->borrowing)
                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $this->borrowing->borrower }}
                    </div>
                     <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Request #{{ $this->borrowing->id }}
                    </div>
                </div>
            @endif

            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 dark:text-red-400" />
                    <span class="text-sm font-medium text-red-900 dark:text-red-200">
                        {{ __('Status akan berubah menjadi Rejected') }}
                    </span>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-button variant="danger">
                    <span wire:loading.remove wire:target="reject">{{ __('Tolak Peminjaman') }}</span>
                    <span wire:loading wire:target="reject" class="flex items-center gap-2">
                         <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        {{ __('Memproses...') }}
                    </span>
                </x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-borrowing-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="destroy" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Hapus Data Peminjaman?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Tindakan ini tidak dapat dibatalkan. Data peminjaman akan dihapus permanen dari sistem.') }}
            </p>

            @if ($this->borrowing)
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Peminjam') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $this->borrowing->borrower }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Tanggal') }}</dt>
                            <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">
                                {{ $this->borrowing->start_datetime->format('d M') }} - {{ $this->borrowing->end_datetime->format('d M') }}
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-trash class="w-5 h-5 text-red-600 dark:text-red-400" />
                    <span class="text-sm font-medium text-red-900 dark:text-red-200">
                        {{ __('Data yang dihapus tidak dapat dipulihkan.') }}
                    </span>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button>
                    <span wire:loading.remove wire:target="destroy">{{ __('Hapus Peminjaman') }}</span>
                    <span wire:loading wire:target="destroy" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-4 w-4 animate-spin" />
                        {{ __('Menghapus...') }}
                    </span>
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
