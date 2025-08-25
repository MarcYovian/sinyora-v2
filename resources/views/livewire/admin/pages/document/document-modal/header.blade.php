<div class="flex-shrink-0">
    <div class="flex justify-between items-start">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                Detail Pengajuan Dokumen
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Diajukan oleh:
                <span class="font-medium text-slate-700 dark:text-slate-300">
                    {{ $this->doc->submitter->name }}
                </span>
            </p>
        </div>
        {{-- Tombol close untuk mobile --}}
        <button x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500 lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
