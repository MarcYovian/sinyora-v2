<div x-data="{ isOpen: true }"
    class="bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
    <div class="p-4">
        <button @click="isOpen = !isOpen" class="w-full flex justify-between items-center text-left">
            <h3 class="font-semibold text-slate-900 dark:text-slate-200">
                Informasi Pengajuan
            </h3>
            {{-- Ikon panah yang berotasi --}}
            <svg class="w-5 h-5 text-slate-500 transform transition-transform" :class="{ 'rotate-180': isOpen }"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="isOpen" x-collapse>
            <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div class="col-span-2">
                    <dt class="text-slate-500 dark:text-slate-400 mb-1">
                        Organisasi
                    </dt>
                    @if ($isEditing)
                        <div class="space-y-2">
                            @foreach ($form->analysisResult['document_information']['emitter_organizations'] ?? [] as $organizationIndex => $organization)
                                <div class="flex items-center gap-2" wire:key="Organization-{{ $organizationIndex }}">
                                    <x-text-input type="text" class="w-full text-sm"
                                        wire:model.defer="form.analysisResult.document_information.emitter_organizations.{{ $organizationIndex }}.name" />
                                    <button type="button" wire:click="removeOrganization({{ $organizationIndex }})"
                                        class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-2 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get(
                                    'form.analysisResult.document_information.emitter_organizations.{{ $organizationIndex }}.name',
                                )" class="mt-2" />
                            @endforeach
                            <x-button type="button" variant="primary" size="sm" wire:click="addOrganization()">
                                + Tambah Organisasi
                            </x-button>
                        </div>
                    @else
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            <ul class="list-disc list-inside">
                                @forelse ($form->analysisResult['document_information']['emitter_organizations'] ?? [] as $organization)
                                    <li>{{ $organization['name'] }}</li>
                                @empty
                                    <li>N/A</li>
                                @endforelse
                            </ul>
                        </dd>
                    @endif
                </div>
                <div class="col-span-2">
                    <dt class="text-slate-500 dark:text-slate-400">Perihal</dt>
                    @if ($isEditing)
                        <div class="space-y-2">
                            @foreach ($form->analysisResult['document_information']['subjects'] ?? [] as $index => $subject)
                                <div class="flex items-center gap-2" wire:key="subject-{{ $index }}">
                                    <x-text-input type="text" class="mt-1 block w-full"
                                        wire:model.defer="form.analysisResult.document_information.subjects.{{ $index }}" />
                                    <button type="button" wire:click="removeSubject({{ $index }})"
                                        class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-2 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get(
                                    'form.analysisResult.document_information.subjects.{{ $index }}',
                                )" class="mt-2" />
                            @endforeach
                            <x-button type="button" variant="primary" size="sm" wire:click="addSubject()">
                                + Tambah Perihal
                            </x-button>
                        </div>
                    @else
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            @if (isset($form->analysisResult['document_information']))
                                {{ implode(', ', $form->analysisResult['document_information']['subjects']) ?: 'N/A' }}
                            @endif
                        </dd>
                    @endif
                </div>
                <div class="col-span-2">
                    @if ($isEditing)
                        <dt class="text-slate-500 dark:text-slate-400">Email</dt>
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            <x-text-input type="text" class="mt-1 block w-full"
                                wire:model.defer="form.analysisResult.document_information.emitter_email" />
                            <x-input-error :messages="$errors->get('form.analysisResult.document_information.emitter_email')" class="mt-2" />
                        </dd>
                    @else
                        <dt class="text-slate-500 dark:text-slate-400">Email</dt>
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            {{ $form->analysisResult['document_information']['emitter_email'] ?? 'N/A' }}
                        </dd>
                    @endif
                </div>
                <div class="col-span-2">
                    @if ($isEditing)
                        <dt class="text-slate-500 dark:text-slate-400">Kota</dt>
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            <x-text-input type="text" class="mt-1 block w-full"
                                wire:model.defer="form.analysisResult.document_information.document_city" />
                            <x-input-error :messages="$errors->get('form.analysisResult.document_information.document_city')" class="mt-2" />
                        </dd>
                    @else
                        <dt class="text-slate-500 dark:text-slate-400">Kota</dt>
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            {{ $form->analysisResult['document_information']['document_city'] ?? 'N/A' }}
                        </dd>
                    @endif
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-slate-400">Nomor Surat</dt>
                    @if ($isEditing)
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            <x-text-input type="text" class="mt-1 block w-full"
                                wire:model.defer="form.analysisResult.document_information.document_number" />
                            <x-input-error :messages="$errors->get('form.analysisResult.document_information.document_number')" class="mt-2" />
                        </dd>
                    @else
                        <dd class="text-slate-800 dark:text-slate-200">
                            {{ $form->analysisResult['document_information']['document_number'] ?? 'N/A' }}
                        </dd>
                    @endif
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-slate-400">Tanggal Surat</dt>
                    @if ($isEditing)
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            <x-text-input type="text" class="mt-1 block w-full"
                                wire:model.defer="form.analysisResult.document_information.document_date" />
                            <x-input-error :messages="$errors->get('form.analysisResult.document_information.document_date')" class="mt-2" />
                        </dd>
                    @else
                        <dd class="text-slate-800 dark:text-slate-200">
                            {{ $form->analysisResult['document_information']['document_date'] ?? 'N/A' }}
                        </dd>
                    @endif
                </div>
                <div class="col-span-2">
                    <dt class="text-slate-500 dark:text-slate-400 mb-1">Penerima Surat</dt>
                    @if ($isEditing)
                        <div class="space-y-2">
                            @foreach ($form->analysisResult['document_information']['recipients'] ?? [] as $recipientIndex => $penerima)
                                <div class="flex items-center gap-2" wire:key="recipient-{{ $recipientIndex }}">
                                    <x-text-input type="text" class="w-full text-sm"
                                        wire:model.defer="form.analysisResult.document_information.recipients.{{ $recipientIndex }}.name" />
                                    <button type="button" wire:click="removeRecipient({{ $recipientIndex }})"
                                        class="text-red-500 hover:text-red-700 dark:hover:text-red-400 p-2 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get(
                                    'form.analysisResult.document_information.recipients.{{ $recipientIndex }}.name',
                                )" class="mt-2" />
                            @endforeach
                            <x-button type="button" variant="primary" size="sm" wire:click="addRecipient()">
                                + Tambah Penerima
                            </x-button>
                        </div>
                    @else
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            <ul class="list-disc list-inside">
                                @forelse ($form->analysisResult['document_information']['recipients'] ?? [] as $penerima)
                                    <li>{{ $penerima['name'] }}</li>
                                @empty
                                    <li>N/A</li>
                                @endforelse
                            </ul>
                        </dd>
                    @endif
                </div>
                <div class="col-span-2">
                    <dt class="text-slate-500 dark:text-slate-400">Jenis Surat</dt>
                    @if ($isEditing)
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            <select wire:model="form.analysisResult.type" id="type"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 py-2 pl-3 pr-10">
                                @foreach (App\Enums\DocumentType::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('form.analysisResult.type')" class="mt-2" />
                        </dd>
                    @else
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            {{ $form->analysisResult['type'] ?? 'N/A' }}
                        </dd>
                    @endif
                </div>
            </dl>
        </div>
    </div>
</div>
