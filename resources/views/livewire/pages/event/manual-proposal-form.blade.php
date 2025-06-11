{{-- Inisialisasi Alpine.js untuk Tab --}}
<form wire:submit.prevent="save">
    <div x-data="{ currentTab: 'dataDiri' }">
        {{-- Navigasi Tab --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                {{-- Tombol Tab 1: Data Diri --}}
                <a href="#" @click.prevent="currentTab = 'dataDiri'"
                    :class="{
                        'border-[#FFD24C] text-[#825700]': currentTab === 'dataDiri',
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== 'dataDiri'
                    }"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    Langkah 1: Data Diri
                </a>

                {{-- Tombol Tab 2: Data Event --}}
                <a href="#" @click.prevent="currentTab = 'dataEvent'"
                    :class="{
                        'border-[#FFD24C] text-[#825700]': currentTab === 'dataEvent',
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': currentTab !== 'dataEvent'
                    }"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    Langkah 2: Detail Kegiatan
                </a>
            </nav>
        </div>

        {{-- Konten Tab 1: Data Diri Guest --}}
        <div x-show="currentTab === 'dataDiri'" class="space-y-4">
            <h3 class="text-md font-medium text-gray-800">Informasi Penanggung Jawab / Kontak</h3>
            <div class="space-y-2">
                <x-input-label for="guest_name" value="Nama Lengkap Anda" />
                <x-text-input wire:model.blur="form.guestName" id="guest_name" type="text" class="block w-full"
                    placeholder="Masukkan nama lengkap" />
                <x-input-error :messages="$errors->get('form.guestName')" />
            </div>
            <div class="space-y-2">
                <x-input-label for="guest_email" value="Alamat Email" />
                <x-text-input wire:model.blur="form.guestEmail" id="guest_email" type="email" class="block w-full"
                    placeholder="cth: emailanda@example.com" />
                <x-input-error :messages="$errors->get('form.guestEmail')" />
            </div>
            <div class="space-y-2">
                <x-input-label for="guest_phone" value="Nomor Telepon (WA)" />
                <x-text-input wire:model.blur="form.guestPhone" id="guest_phone" type="text" class="block w-full"
                    placeholder="cth: 081234567890" />
                <x-input-error :messages="$errors->get('form.guestPhone')" />
            </div>

            {{-- Tombol Navigasi ke Tab Berikutnya --}}
            <div class="flex justify-end pt-4">
                <button type="button" @click="currentTab = 'dataEvent'"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#825700] hover:bg-[#6b4900] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#825700]">
                    Selanjutnya
                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Konten Tab 2: Data Event --}}
        {{-- Konten Tab 2: Data Event (UI/UX Baru) --}}
        <div x-show="currentTab === 'dataEvent'" class="space-y-8">

            {{-- BAGIAN 1: INFORMASI DASAR --}}
            <div class="p-5 border border-gray-200 rounded-lg">
                <div class="flex items-center mb-4">
                    <div
                        class="flex-shrink-0 bg-indigo-100 text-indigo-600 rounded-full h-8 w-8 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Informasi Dasar Kegiatan</h3>
                </div>

                <div class="space-y-4">
                    <div>
                        <x-input-label for="name" value="Nama Kegiatan" />
                        <x-text-input wire:model="form.name" id="name" type="text" class="block w-full mt-1"
                            placeholder="Contoh: Misa Syukur Awal Tahun" />
                        <x-input-error :messages="$errors->get('form.name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Deskripsi Singkat" />
                        <textarea wire:model="form.description" id="description" rows="3"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Jelaskan tujuan dan gambaran singkat kegiatan..."></textarea>
                        <x-input-error :messages="$errors->get('form.description')" class="mt-1" />
                    </div>
                </div>
            </div>

            {{-- BAGIAN 2: JADWAL & KATEGORI --}}
            <div class="p-5 border border-gray-200 rounded-lg">
                <div class="flex items-center mb-4">
                    <div
                        class="flex-shrink-0 bg-green-100 text-green-600 rounded-full h-8 w-8 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Detail Jadwal & Kategori</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <x-input-label for="event_category_id" value="Kategori" />
                        <x-select wire:model="form.event_category_id" id="event_category_id" class="mt-1 w-full">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('form.event_category_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="organization_id" value="Seksi/Panitia" />
                        <x-select wire:model="form.organization_id" id="organization_id" class="mt-1 w-full">
                            <option value="">Pilih Seksi/Panitia</option>
                            @foreach ($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('form.organization_id')" class="mt-1" />
                    </div>

                    <div class="md:col-span-2 pt-2">
                        <x-input-label value="Rentang Waktu Kegiatan" />
                    </div>
                    <div>
                        <x-text-input wire:model="form.start_datetime" id="start_datetime" type="datetime-local"
                            class="w-full" />
                        <x-input-error :messages="$errors->get('form.start_datetime')" class="mt-1" />
                    </div>

                    <div>
                        <x-text-input wire:model="form.end_datetime" id="end_datetime" type="datetime-local"
                            class="w-full" />
                        <x-input-error :messages="$errors->get('form.end_datetime')" class="mt-1" />
                    </div>
                </div>
            </div>

            {{-- BAGIAN 3: LOKASI --}}
            <div class="p-5 border border-gray-200 rounded-lg">
                <div class="flex items-center mb-4">
                    <div
                        class="flex-shrink-0 bg-yellow-100 text-yellow-600 rounded-full h-8 w-8 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-gray-900">Pilih Lokasi Kegiatan</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach ($locations as $location)
                        <x-checkbox-card wire:model="form.locations" value="{{ $location->id }}"
                            class="p-3 border rounded-lg transition-all duration-150 hover:border-[#825700] hover:bg-[#FFD24C]/10">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">{{ $location->name }}</h3>
                                    <p class="text-sm text-gray-500 line-clamp-2">
                                        {{ $location->description }}</p>
                                </div>
                            </div>
                        </x-checkbox-card>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('form.locations')" class="mt-2" />
            </div>


            {{-- Tombol Navigasi Kembali --}}
            <div class="flex justify-start pt-2">
                <button type="button" @click="currentTab = 'dataDiri'"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Kembali
                </button>
            </div>
        </div>

    </div>

    <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4 dark:border-gray-700">
        <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">
            Batal
        </x-secondary-button>

        <x-primary-button type="submit" class="w-full sm:w-auto justify-center" wire:loading.attr="disabled"
            wire:target="syncPermission">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"
                wire:loading.remove>
                <path
                    d="M2.003 5.884L10 2l7.997 3.884A2 2 0 0019 7.616l-7.5 3.232a3 3 0 01-3 0L1 7.616a2 2 0 00-1.997-1.732z" />
                <path d="M1 9.616l7.5 3.232a3 3 0 003 0L19 9.616V14a2 2 0 01-2 2H3a2 2 0 01-2-2V9.616z" />
            </svg>
            <div wire:loading wire:target="save"
                class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            <span wire:loading.remove>Ajukan Proposal</span>
            <span wire:loading>Mengajukan...</span>
        </x-primary-button>
    </div>
</form>
