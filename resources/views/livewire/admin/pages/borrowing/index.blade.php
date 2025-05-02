<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Borrowings') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            @can('create asset borrowing')
                <x-button type="button" variant="primary" href="{{ route('admin.asset-borrowings.create') }}"
                    class="items-center max-w-xs gap-2">
                    <x-heroicon-s-plus class="w-5 h-5" />

                    <span>{{ __('Create') }}</span>
                </x-button>
            @endcan

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search borrowings by date.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Borrowings" :heads="$table_heads">
                @forelse ($borrowings as $key => $borrowing)
                    <tr wire:key="user-{{ $borrowing->id }}" x-on:dblclick="$wire.show({{ $borrowing->id }})"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $borrowings->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $borrowing->borrower }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $borrowing->event ? $borrowing->event->name : '-' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $borrowing->start_datetime->format('d/m/Y H:i') . ' - ' . $borrowing->end_datetime->format('d/m/Y H:i') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $borrowing->notes }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            @if ($borrowing->status === App\Enums\BorrowingStatus::APPROVED)
                                <span
                                    class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-green-900 dark:text-green-300">
                                    {{ __('Approved') }}
                                </span>
                            @elseif ($borrowing->status === App\Enums\BorrowingStatus::PENDING)
                                <span
                                    class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-yellow-900 dark:text-yellow-300">
                                    {{ __('Pending') }}
                                </span>
                            @elseif ($borrowing->status === App\Enums\BorrowingStatus::REJECTED)
                                <span
                                    class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-red-900 dark:text-red-300">
                                    {{ __('Rejected') }}
                                </span>
                            @else
                                <span
                                    class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-gray-900 dark:text-gray-300">
                                    {{ __('Unknown') }}
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            <div class="flex flex-col items-center gap-2">
                                @can('view asset borrowing details')
                                    <x-button size="sm" variant="primary" wire:click="show({{ $borrowing }})">
                                        {{ __('Detail') }}
                                    </x-button>
                                @endcan

                                @can('edit asset borrowing')
                                    <x-button size="sm" variant="warning" type="button"
                                        disabled="{{ $borrowing->status === App\Enums\BorrowingStatus::APPROVED }}"
                                        href="{{ route('admin.asset-borrowings.edit', $borrowing) }}">
                                        {{ __('Edit') }}
                                    </x-button>
                                @endcan

                                @can('delete asset borrowing')
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmDelete({{ $borrowing->id }})">
                                        {{ __('Delete') }}
                                    </x-button>
                                @endcan

                                @if ($borrowing->status === App\Enums\BorrowingStatus::PENDING)
                                    @can('asset borrowing approve')
                                        <x-button size="sm" variant="success" type="button"
                                            wire:click="confirmApprove({{ $borrowing->id }})">
                                            {{ __('Approve') }}
                                        </x-button>
                                    @endcan
                                    @can('asset borrowing reject')
                                        <x-button size="sm" variant="danger" type="button"
                                            wire:click="confirmReject({{ $borrowing->id }})">
                                            {{ __('Reject') }}
                                        </x-button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white dark:bg-gray-800">
                        <td colspan="{{ count($table_heads) }}"
                            class="whitespace-nowrap px-6 py-4 text-rose-700 dark:text-rose-400 text-sm text-center">
                            {{ __('No data available') }}
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>
        <div class="px-6 py-4">
            {{ $borrowings->links() }}
        </div>
    </div>

    <x-modal name="borrowing-detail-modal" maxWidth="5xl" focusable>
        @if ($this->borrowing)
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Borrowing Request #{{ $this->borrowing->id ?? 'N/A' }}
                    </h2>
                    <button type="button" @click="$dispatch('close')"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
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
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</p>
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
                                            {{ substr($this->borrowing->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $this->borrowing->user->name }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $this->borrowing->user->email }}
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
                                            {{ $this->borrowing->event->name }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($this->borrowing->event->start_datetime)->format('M j, Y g:i A') }}
                                            -
                                            {{ \Carbon\Carbon::parse($this->borrowing->event->end_datetime)->format('M j, Y g:i A') }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                            {{ $this->borrowing->event->description }}
                                        </p>
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
                                        @can('asset borrowing approve')
                                            <x-button variant="success" class="w-full"
                                                wire:click="confirmApprove({{ $this->borrowing->id }})">
                                                <x-heroicon-s-check class="h-5 w-5 mr-2" />
                                                Approve Request
                                            </x-button>
                                        @endcan
                                        @can('asset borrowing reject')
                                            <x-button variant="danger" class="w-full"
                                                wire:click="confirmReject({{ $this->borrowing->id }})">
                                                <x-heroicon-s-x-mark class="h-5 w-5 mr-2" />
                                                Reject Request
                                            </x-button>
                                        @endcan
                                    @endif

                                    @can('edit asset borrowing')
                                        <x-button variant="secondary" class="w-full" href="#">
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
        <form wire:submit="approve">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 rounded-full bg-green-100 dark:bg-green-800 text-green-600 dark:text-green-300">
                        <x-heroicon-s-check class="h-6 w-6" />
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        Confirm Approval
                    </h2>
                </div>

                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Are you sure you want to approve this borrowing request? This will reserve the assets for the
                    borrower.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-button variant="secondary" size="sm" x-on:click="$dispatch('close')">
                        Cancel
                    </x-button>
                    <x-button variant="success" size="sm">
                        Confirm Approval
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>

    <x-modal name="reject-borrowing-confirmation" focusable>
        <form wire:submit="reject">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 rounded-full bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-300">
                        <x-heroicon-s-x-mark class="h-6 w-6" />
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        Confirm Rejection
                    </h2>
                </div>

                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Are you sure you want to reject this borrowing request? This will cancel the request.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-button variant="secondary" size="sm" x-on:click="$dispatch('close')">
                        Cancel
                    </x-button>
                    <x-button variant="danger" size="sm">
                        Confirm Rejection
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-borrowing-confirmation" focusable>
        @if ($this->borrowing)
            <form wire:submit="destroy">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-full bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-300">
                            <x-heroicon-s-x-mark class="h-6 w-6" />
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Confirm Deletion
                        </h2>
                    </div>

                    @if ($this->borrowing->status === App\Enums\BorrowingStatus::PENDING)
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Are you sure you want to delete this borrowing request? This will permanently delete the
                            request.
                        </p>
                    @else
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Are you sure you want to cancel this borrowing request? This will cancel the request.
                        </p>
                    @endif

                    <div class="mt-6 flex justify-end gap-3">
                        <x-button variant="secondary" size="sm" x-on:click="$dispatch('close')">
                            Cancel
                        </x-button>
                        <x-button variant="danger" size="sm">
                            Confirm Deletion
                        </x-button>
                    </div>
                </div>
            </form>
        @endif
    </x-modal>
</div>
