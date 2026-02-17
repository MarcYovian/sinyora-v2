<x-app-layout>
    <div class="py-12" x-data="{ activeTab: 'general' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Sidebar Tabs -->
                <div class="col-span-1">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 px-4">{{ __('Settings') }}</h2>
                    <nav class="flex flex-col space-y-1">
                        <button @click="activeTab = 'general'" 
                            :class="{ 'bg-white dark:bg-gray-800 shadow-sm text-gray-900 dark:text-white ring-1 ring-gray-900/5 dark:ring-white/10': activeTab === 'general', 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white': activeTab !== 'general' }"
                            class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                            <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="{ 'text-indigo-500': activeTab === 'general', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'general' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('General') }}
                        </button>

                        <button @click="activeTab = 'notifications'" 
                            :class="{ 'bg-white dark:bg-gray-800 shadow-sm text-gray-900 dark:text-white ring-1 ring-gray-900/5 dark:ring-white/10': activeTab === 'notifications', 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white': activeTab !== 'notifications' }"
                            class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                            <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="{ 'text-indigo-500': activeTab === 'notifications', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'notifications' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            {{ __('Notifications') }}
                        </button>

                        <button @click="activeTab = 'security'" 
                            :class="{ 'bg-white dark:bg-gray-800 shadow-sm text-gray-900 dark:text-white ring-1 ring-gray-900/5 dark:ring-white/10': activeTab === 'security', 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white': activeTab !== 'security' }"
                            class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                            <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="{ 'text-indigo-500': activeTab === 'security', 'text-gray-400 group-hover:text-gray-500': activeTab !== 'security' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            {{ __('Security') }}
                        </button>
                    </nav>
                </div>

                <!-- Content Area -->
                <div class="col-span-1 md:col-span-3">
                    <!-- General Tab -->
                    <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                         <!-- Profile Information -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="p-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-1">{{ __('Profile') }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('These informations will be displayed publicly.') }}</p>
                                <livewire:profile.update-profile-information-form />
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Tab -->
                    <div x-show="activeTab === 'notifications'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">{{ __('Notification channels') }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('Where can we notify you?') }}</p>

                            <div class="space-y-6">
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-neutral-900 rounded-lg border border-gray-100 dark:border-gray-700">
                                    <div>
                                        <h4 class="text-base font-medium text-gray-900 dark:text-gray-100">{{ __('Email') }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Receive a daily email digest.') }}</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-500"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-neutral-900 rounded-lg border border-gray-100 dark:border-gray-700">
                                    <div>
                                        <h4 class="text-base font-medium text-gray-900 dark:text-gray-100">{{ __('Desktop') }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Receive desktop notifications.') }}</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-500"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Tab -->
                    <div x-show="activeTab === 'security'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="space-y-6">
                            <!-- Password -->
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-1">{{ __('Password') }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('Confirm your current password before setting a new one.') }}</p>
                                <livewire:profile.update-password-form />
                            </div>
                             <!-- Delete Account -->
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-1">{{ __('Account') }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('No longer want to use our service? You can delete your account here. This action is not reversible. All information related to this account will be deleted permanently.') }}</p>
                                <livewire:profile.delete-user-form />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
