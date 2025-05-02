<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $event->name }}</h1>
                <div class="flex items-center gap-2 mt-2">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $event->status->color() }}">
                        {{ $event->status->label() }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Created {{ $event->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <x-button variant="secondary" :href="route('admin.events.index')">
                    Back to Events
                </x-button>
                <x-button variant="primary" type="button" wire:click="confirmEdit({{ $event->id }})">
                    Edit Event
                </x-button>
            </div>
        </div>
        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Event Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Event Description</h2>
                        <div class="prose dark:prose-invert max-w-none text-sm text-gray-500 dark:text-gray-400">
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    </div>
                </div>

                <!-- Schedule Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Schedule Details</h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Recurrence Type</p>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">
                                        {{ $event->recurrence_type->label() }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Date Range</p>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">
                                        {{ $event->start_recurring->format('M j, Y') }} -
                                        {{ $event->end_recurring->format('M j, Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Time Range</p>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">
                                        {{ $event->eventRecurrences->first()->time_start->format('g:i A') }} -
                                        {{ $event->eventRecurrences->first()->time_end->format('g:i A') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Recurrences Table -->
                            @if ($event->recurrence_type !== \App\Enums\EventRecurrenceType::CUSTOM)
                                <div class="mt-6">
                                    <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">Upcoming
                                        Occurrences</h3>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Date</th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Day</th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Time</th>
                                                </tr>
                                            </thead>
                                            <tbody
                                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach ($event->eventRecurrences->take(5) as $recurrence)
                                                    <tr>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                            {{ $recurrence->date->format('M j, Y') }}
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $recurrence->date->format('l') }}
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                            {{ $recurrence->time_start->format('g:i A') }} -
                                                            {{ $recurrence->time_end->format('g:i A') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if ($event->eventRecurrences->count() > 5)
                                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            + {{ $event->eventRecurrences->count() - 5 }} more occurrences
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Locations Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Event Locations</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($event->locations as $location)
                                <div
                                    class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow dark:border-gray-700">
                                    <div class="flex">
                                        @if ($location->image)
                                            <div class="flex-shrink-0">
                                                <img class="h-32 w-32 object-cover"
                                                    src="{{ Storage::url($location->image) }}"
                                                    alt="{{ $location->name }}">
                                            </div>
                                        @endif
                                        <div class="p-4">
                                            <h3 class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $location->name }}</h3>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $location->description }}</p>
                                            <div class="mt-2">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $location->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
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
                <!-- Category & Organization Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Event Information</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</p>
                                <div class="mt-1 flex items-center">
                                    <span class="h-4 w-4 rounded-full mr-2"
                                        style="background-color: {{ $event->eventCategory->color }}"></span>
                                    <span
                                        class="text-gray-900 dark:text-gray-100">{{ $event->eventCategory->name }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</p>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $event->organization->name }}</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $event->organization->description }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</p>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $event->created_by }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status History Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Approval Status</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Status</p>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        {{ $event->status->color() }}">
                                        {{ $event->status->label() }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Event Actions</h2>
                        <div class="space-y-2">
                            <x-button variant="primary" class="w-full"
                                href="{{ route('admin.events.recurrences.index', $event) }}">
                                Edit Event Details
                            </x-button>

                            <x-button variant="danger" class="w-full" type="button"
                                wire:click="confirmDelete({{ $event->id }})">
                                Delete Event
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="event-modal" :show="$errors->isNotEmpty()" maxWidth="3xl" focusable>
        <form wire:submit="save" class="p-6">
            <!-- Header with close button -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    {{ __('Edit Event') }}
                </h2>
                <button type="button" @click="$dispatch('close')"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                    <x-heroicon-s-x-mark class="h-6 w-6" />
                </button>
            </div>

            <!-- Global Form Error -->
            @if ($errors->isNotEmpty())
                <div
                    class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
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
                        {{ __('Update Event') }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="h-5 w-5 animate-spin" />
                        {{ __('Saving...') }}
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="update-event-confirmation" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="edit" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to update this event?') }}
            </h2>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Once the event is updated, all details will be updated.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Update') }}
                </x-danger-button>
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
</div>
