<div x-data="{ submissionType: 'manual' }">
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
    <div x-data="{}">

        <h2 class="text-lg font-medium text-gray-900 mb-2">
            Formulir Pengajuan Kegiatan
        </h2>
        <p class="text-sm text-gray-600 mb-6">
            Pilih metode pengajuan. Anda dapat mengisi formulir secara manual atau mengunggah dokumen proposal.
        </p>

        {{-- Pemilih Tipe Pengajuan --}}
        <div class="mb-6">
            <div class="flex border border-gray-200 rounded-lg p-1">
                <button type="button" @click="submissionType = 'manual'"
                    :class="{ 'bg-[#825700] text-white': submissionType === 'manual', 'text-gray-600 hover:bg-gray-50': submissionType !== 'manual' }"
                    class="w-1/2 py-2 px-4 rounded-md text-sm font-medium focus:outline-none transition-colors">
                    Input Manual
                </button>
                <button type="button" @click="submissionType = 'upload'"
                    :class="{ 'bg-[#825700] text-white': submissionType === 'upload', 'text-gray-600 hover:bg-gray-50': submissionType !== 'upload' }"
                    class="w-1/2 py-2 px-4 rounded-md text-sm font-medium focus:outline-none transition-colors">
                    Upload Dokumen
                </button>
            </div>
        </div>

        {{-- Form Input Manual --}}
        <div x-show="submissionType === 'manual'" x-transition>
            <livewire:pages.event.manual-proposal-form />
        </div>

        {{-- Form Upload Dokumen --}}
        <div x-show="submissionType === 'upload'" x-transition class="space-y-6">
            <livewire:pages.event.document-proposal-form />
        </div>
    </div>
</div>
