<div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4">
    <h4 class="font-semibold text-slate-900 dark:text-slate-200 mb-2">Penanda
        Tangan</h4>

    @if ($isEditing)
        <div class="space-y-3">
            @foreach ($form->analysisResult['signature_blocks'] as $signerIndex => $signer)
                <div wire:key="signer-{{ $signerIndex }}"
                    class="p-2 rounded-md border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-end">
                        <button type="button" wire:click="removeSigner({{ $signerIndex }})"
                            class="text-red-500 hover:text-red-700 -mr-2 -mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="signer-nama-{{ $signerIndex }}" value="Nama" />
                            <x-text-input id="signer-nama-{{ $signerIndex }}" type="text"
                                class="mt-1 w-full text-sm"
                                wire:model.defer="form.analysisResult.signature_blocks.{{ $signerIndex }}.name" />
                            <x-input-error :messages="$errors->get('form.analysisResult.signature_blocks.{{ $signerIndex }}.name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="signer-jabatan-{{ $signerIndex }}" value="Jabatan" />
                            <x-text-input id="signer-jabatan-{{ $signerIndex }}" type="text"
                                class="mt-1 w-full text-sm"
                                wire:model.defer="form.analysisResult.signature_blocks.{{ $signerIndex }}.position" />
                            <x-input-error :messages="$errors->get(
                                'form.analysisResult.signature_blocks.{{ $signerIndex }}.position',
                            )" class="mt-2" />
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="pt-2">
                <x-button type="button" variant="outline-primary" size="sm" wire:click="addSigner()">
                    + Tambah Penanda Tangan
                </x-button>
            </div>
        </div>
    @else
        <ul class="space-y-3">
            @forelse ($form->analysisResult['signature_blocks'] ?? [] as $signer)
                <li>
                    <p class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ $signer['name'] }}</p>
                    <p class="text-slate-500 dark:text-slate-400">
                        {{ Str::title($signer['position']) }}</p>
                </li>
            @empty
                <li>Tidak ada data penanda tangan.</li>
            @endforelse
        </ul>
    @endif
</div>
