<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Data Events') }}
        </h2>
    </header>

    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4 px-4 md:px-0 md:flex md:justify-between">
            <x-button type="button" variant="primary" wire:click="create" class="items-center max-w-xs gap-2">
                <x-heroicon-s-plus class="w-5 h-5" />

                <span>{{ __('Create') }}</span>
            </x-button>

            <div class="w-full md:w-1/2">
                <x-search placeholder="Search event by name.." />
            </div>
        </div>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <x-table title="Data Events" :heads="$table_heads">
                @forelse ($events as $key => $event)
                    <tr wire:key="user-{{ $event->id }}"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $key + $events->firstItem() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $event->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ Carbon\Carbon::parse($event->start_recurring)->translatedFormat('l, d F Y') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ Carbon\Carbon::parse($event->end_recurring)->translatedFormat('l, d F Y') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $event->organization->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $event->eventCategory->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            @foreach ($event->locations as $location)
                                <span>{{ $location->name }}</span>
                            @endforeach
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            {{ $event->recurrence_type }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                            @if ($event->status === App\Enums\EventApprovalStatus::APPROVED)
                                <span
                                    class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-green-900 dark:text-green-300">
                                    {{ __('Approved') }}
                                </span>
                            @elseif ($event->status === App\Enums\EventApprovalStatus::PENDING)
                                <span
                                    class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-yellow-900 dark:text-yellow-300">
                                    {{ __('Pending') }}
                                </span>
                            @elseif ($event->status === App\Enums\EventApprovalStatus::REJECTED)
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
                                <x-button size="sm" variant="primary"
                                    href="{{ route('admin.events.show', $event) }}">
                                    {{ __('Detail') }}
                                </x-button>
                                <x-button size="sm" variant="warning" type="button"
                                    disabled="{{ $event->status === App\Enums\EventApprovalStatus::APPROVED }}"
                                    wire:click="edit({{ $event->id }})">
                                    {{ __('Edit') }}
                                </x-button>
                                <x-button size="sm" variant="danger" type="button"
                                    wire:click="confirmDelete({{ $event->id }})">
                                    {{ __('Delete') }}
                                </x-button>
                                @if ($event->status === App\Enums\EventApprovalStatus::PENDING)
                                    <x-button size="sm" variant="success" type="button"
                                        wire:click="confirmApprove({{ $event->id }})">
                                        {{ __('Approve') }}
                                    </x-button>
                                    <x-button size="sm" variant="danger" type="button"
                                        wire:click="confirmReject({{ $event->id }})">
                                        {{ __('Reject') }}
                                    </x-button>
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
            {{ $events->links() }}
        </div>
    </div>

    <x-modal name="event-modal" :show="$errors->isNotEmpty()" maxWidth="3xl" focusable>
        <form wire:submit="save" class="p-6">
            <!-- Header with close button -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    {{ $editId ? __('Edit Event') : __('Create New Event') }}
                </h2>
                <button type="button" @click="$dispatch('close')"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <!-- Global Form Error -->
            @if ($errors->isNotEmpty())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                        <x-heroicon-s-exclamation-circle class="h-5 w-5" />
                        <h3 class="font-medium">{{ __('There are errors in your form submission') }}</h3>
                    </div>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-6">
                <!-- Basic Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="md:col-span-2">
                        <x-input-label for="name" value="{{ __('Event Name') }}" />
                        <x-text-input wire:model="form.name" id="name" type="text"
                            class="block w-full mt-2 px-4 py-2.5"
                            placeholder="{{ __('e.g. Annual Conference, Team Meeting') }}" />
                        <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <x-input-label for="description" value="{{ __('Description') }}" />
                        <textarea wire:model="form.description" id="description" rows="3"
                            class="block w-full mt-2 px-4 py-2.5 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('Brief description about the event...') }}"></textarea>
                        <x-input-error :messages="$errors->get('form.description')" class="mt-2" />
                    </div>

                    <!-- Category & Organization -->
                    <div>
                        <x-input-label for="event_category_id" value="{{ __('Category') }}" />
                        <x-select wire:model="form.event_category_id" id="event_category_id" class="mt-2 w-full">
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('form.event_category_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="organization_id" value="{{ __('Organization') }}" />
                        <x-select wire:model="form.organization_id" id="organization_id" class="mt-2 w-full">
                            <option value="">{{ __('Select Organization') }}</option>
                            @foreach ($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('form.organization_id')" class="mt-2" />
                    </div>
                </div>

                <!-- DateTime Section -->
                <div class="space-y-2">
                    <x-input-label value="{{ __('Datetime Range') }}" />
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="start_datetime" value="{{ __('Start Date') }}" class="sr-only" />
                            <x-text-input wire:model="form.start_datetime" id="start_datetime" type="datetime-local"
                                placeholder="{{ __('Start date') }}" class="w-full" />
                            <x-input-error :messages="$errors->get('form.start_datetime')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="end_datetime" value="{{ __('End Date') }}" class="sr-only" />
                            <x-text-input wire:model="form.end_datetime" id="end_datetime" type="datetime-local"
                                placeholder="{{ __('End date') }}" class="w-full" />
                            <x-input-error :messages="$errors->get('form.end_datetime')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Recurrence Type -->
                <div>
                    <x-input-label value="{{ __('Recurrence Pattern') }}" />
                    <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach (App\Enums\EventRecurrenceType::cases() as $type)
                            <x-radio-card wire:model="form.recurrence_type" value="{{ $type->value }}"
                                class="flex-col items-center p-4">
                                <div class="mb-2 text-indigo-600 dark:text-indigo-400">
                                    @switch($type)
                                        @case(App\Enums\EventRecurrenceType::DAILY)
                                            <x-heroicon-s-calendar-days class="h-6 w-6" />
                                        @break

                                        @case(App\Enums\EventRecurrenceType::WEEKLY)
                                            <x-heroicon-s-arrow-path-rounded-square class="h-6 w-6" />
                                        @break

                                        @case(App\Enums\EventRecurrenceType::BIWEEKLY)
                                            <x-heroicon-s-arrows-right-left class="h-6 w-6" />
                                        @break

                                        @case(App\Enums\EventRecurrenceType::MONTHLY)
                                            <x-heroicon-s-calendar class="h-6 w-6" />
                                        @break

                                        @case(App\Enums\EventRecurrenceType::CUSTOM)
                                            <x-heroicon-s-cog-6-tooth class="h-6 w-6" />
                                        @break
                                    @endswitch
                                </div>
                                <span class="text-sm font-medium text-center">{{ $type->label() }}</span>
                            </x-radio-card>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('form.recurrence_type')" class="mt-2" />
                </div>

                <!-- Locations -->
                <div>
                    <x-input-label value="{{ __('Locations') }}" />
                    <div class="mt-2 space-y-2">
                        @foreach ($locations as $location)
                            <x-checkbox-card wire:model="form.locations" value="{{ $location->id }}"
                                class="p-3 border rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $location->name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                            {{ $location->description }}</p>
                                    </div>
                                    @if ($location->image)
                                        <img src="{{ Storage::url($location->image) }}" alt="{{ $location->name }}"
                                            class="h-12 w-12 rounded object-cover">
                                    @endif
                                </div>
                            </x-checkbox-card>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('form.locations')" class="mt-2" />
                </div>
            </div>

            <!-- Footer Buttons -->
            <div
                class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" @click="$dispatch('close')"
                    class="w-full sm:w-auto justify-center px-6 py-3">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button type="submit"
                    class="w-full sm:w-auto justify-center px-6 py-3 shadow-lg hover:shadow-xl transition-shadow">
                    <span wire:loading.remove wire:target="save">
                        {{ $editId ? __('Update Event') : __('Create Event') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                        {{ __('Saving...') }}
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="delete-event-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="delete" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete this event?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please confirm that you want to delete this event by clicking the button below.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="approve-event-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="approve" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to approve this event?') }}
            </h2>


            @if ($errors->isNotEmpty())
                <div class="mt-6 p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-300"
                    role="alert">
                    <span class="font-medium">Error!</span> {{ $errors->first() }}
                </div>
            @else
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('This action cannot be undone.') }}
                </p>
            @endif

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Approve') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="reject-event-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="reject" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to reject this event?') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Reject') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</div>
