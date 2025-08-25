<div>
    <x-modal name="document-modal" maxWidth="7xl" focusable>
        @if ($this->doc)
            <div x-data="{ view: 'detail' }" class="h-[90vh] bg-white dark:bg-gray-900 flex flex-col">

                {{-- NAVIGASI TAB (HANYA MUNCUL DI MOBILE) --}}
                <div class="lg:hidden border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <nav class="flex -mb-px">
                        <button @click="view = 'detail'" type="button"
                            :class="view === 'detail' ? 'border-blue-500 text-blue-600' :
                                'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm text-center">
                            Detail Informasi
                        </button>
                        <button @click="view = 'preview'" type="button"
                            :class="view === 'preview' ? 'border-blue-500 text-blue-600' :
                                'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm text-center">
                            Pratinjau Dokumen
                        </button>
                    </nav>
                </div>

                <div class="flex-grow grid grid-cols-1 lg:grid-cols-12 overflow-hidden">

                    {{-- KOLOM KIRI (PANEL DETAIL) --}}
                    <div class="lg:col-span-5 p-4 sm:p-6 flex flex-col overflow-y-auto"
                        :class="view === 'detail' ? 'flex' : 'hidden'">
                        @include('livewire.admin.pages.document.document-modal.header')

                        {{-- KONTEN UTAMA (SCROLLABLE) --}}
                        <div class="mt-6 space-y-5 flex-grow pr-2">
                            @include('livewire.admin.pages.document.document-modal.submission-info')
                            @include('livewire.admin.pages.document.document-modal.ai-analysis')
                        </div>

                        {{-- FOOTER PANEL KIRI --}}
                        <div @class([
                            'mt-auto pt-4 flex-shrink-0',
                            'sticky bottom-0 -mx-6 px-6 -mb-6 pb-6 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700' => $isEditing,
                        ])>
                            @include('livewire.admin.pages.document.document-modal.footer-actions')
                        </div>
                    </div>

                    {{-- KOLOM KANAN (PANEL VIEWER) --}}
                    <div class="lg:col-span-7 flex flex-col" x-show="view === 'preview' || window.innerWidth >= 1024"
                        :class="{ 'lg:flex': true }">
                        @include('livewire.admin.pages.document.document-modal.viewer')
                    </div>
                </div>
            </div>
        @else
            {{-- SKELETON JIKA DATA BELUM SIAP --}}
            @include('livewire.admin.pages.document.document-modal.skeleton')
        @endif
    </x-modal>
</div>
