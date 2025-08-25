<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class DocumentCorrectionForm extends Form
{
    public ?int $id = null;
    public ?string $type = null;
    public array $document_information = [
        'document_city' => null,
        'document_date' => [
            'status' => null,
            'type' => null,
            'date' => null,
            'message' => null
        ],
        'document_number' => null,
        'emitter_email' => null,
        'emitter_organizations' => [
            [
                "name" => null,
                "original_name" => null,
                "nama_organisasi" => null,
                "nama_organisasi_id" => null,
                "match_status" => null,
                "similarity_score" => null
            ]
        ],
        'recipients' => [
            [
                "name" => null,
                "position" => null,
            ]
        ],
        'subject' => [null],
        'final_organization_id' => null
    ];
    public array $events = [[
        'eventName' => null,
        'date' => null,
        'time' => null,
        'location' => null,
        'equipment' => [
            [
                "item" => "Salib Prosesi",
                "quantity" => 1,
                "original_name" => "Salib Prosesi",
                "item_id" => 14,
                "match_status" => "matched",
                "similarity_score" => 0.91
            ]
        ],
        'attendees' => null,
        'organizers' => [
            [
                'name' => null,
                'contact' => null
            ]
        ],
        'schedule' => [
            [
                'description' => null,
                'duration' => null,
                'endTime' => null,
                'startTime' => null,
                'parsed_start_time' => [
                    'status' => null,
                    'time' => null,
                    'message' => null
                ],
                'parsed_end_time' => [
                    'status' => null,
                    'time' => null,
                    'message' => null
                ],
            ],
        ],
        'parsed_dates' => [
            "status" => null,
            'dates' => [
                [
                    'start' => null,
                    'end' => null
                ]
            ]
        ],
        'fivetask_categories' => [
            'name' => null,
            'id' => null
        ],
        'location_data' => [
            [
                'original_name' => null,
                'name' => null,
                'location_id' => null,
                'match_status' => null,
                'similarity_score' => null,
                'source' => null
            ]
        ]
    ]];
    public array $signature_blocks = [[
        'name' => null,
        'position' => null
    ]];


    public function rules()
    {
        return [
            // --- Document Information Validation ---
            'document_information.document_date.date' => ['required', 'date'],
            'document_information.final_organization_id' => ['required', 'exists:organizations,id'],

            // --- Events Validation ---
            'events' => 'required|array|min:1',
            'events.*.eventName' => 'required|string|max:255',
            'events.*.fivetask_categories.name' => 'required|string',
            'events.*.parsed_dates.dates' => 'required|array|min:1',
            'events.*.parsed_dates.dates.*.start' => ['required', 'date_format:Y-m-d\TH:i', 'before_or_equal:events.*.parsed_dates.dates.*.end'],
            'events.*.parsed_dates.dates.*.end' => ['required', 'date_format:Y-m-d\TH:i', 'after_or_equal:events.*.parsed_dates.dates.*.start'],

            'events.*' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $hasMatchedLocation = collect(data_get($value, 'location_data'))->contains('match_status', 'matched');
                    $hasRawLocation = !empty(data_get($value, 'location'));
                    if (!$hasMatchedLocation && !$hasRawLocation) {
                        $fail("Lokasi acara untuk '{$value['eventName']}' wajib diisi atau dipilih dari daftar.");
                    }
                },
            ],

            // --- Schedule/Rundown Validation ---
            'events.*.schedule.*.description' => 'required|string|max:500',
            'events.*.schedule.*.startTime_processed.time' => ['nullable', 'date_format:H:i', 'before_or_equal:events.*.schedule.*.endTime_processed.time'],
            'events.*.schedule.*.endTime_processed.time' => ['nullable', 'date_format:H:i', 'after_or_equal:events.*.schedule.*.startTime_processed.time'],

            // --- Equipment Validation ---
            'events.*.equipment.*.quantity' => 'required|integer|min:1',
            'events.*.equipment.*.item_id' => ['required', 'exists:assets,id'],

            // --- Location Validation ---
            'events.*.location_data.*.name' => ['required', 'string', 'max:255'],
            'events.*.location_data.*.source' => ['required', 'in:location,custom'],
            'events.*.location_data.*.location_id' => [
                Rule::when(fn() => data_get($this->events, '*.location_data.*.source') === 'custom', [
                    'required',
                    'exists:locations,id'
                ])
            ],
        ];
    }

    protected function messages()
    {
        return [
            'document_information.document_date.date.required' => 'Tanggal Dokumen wajib diisi.',
            'document_information.document_date.date.date' => 'Format Tanggal Dokumen tidak valid.',
            'document_information.final_organization_id.required' => 'Organisasi Penerbit wajib dipilih.',
            'document_information.final_organization_id.exists' => 'Organisasi Penerbit tidak valid.',

            'events.required' => 'Detail Acara tidak boleh kosong.',
            'events.*.eventName.required' => 'Nama Acara wajib diisi.',
            'events.*.parsed_dates.dates.*.start.required' => 'Tanggal mulai kegiatan wajib diisi.',
            'events.*.parsed_dates.dates.*.end.required' => 'Tanggal selesai kegiatan wajib diisi.',
            'events.*.parsed_dates.dates.*.start.date_format' => 'Format tanggal mulai kegiatan tidak valid.',
            'events.*.parsed_dates.dates.*.end.date_format' => 'Format tanggal selesai kegiatan tidak valid.',
            'events.*.parsed_dates.dates.*.start.before_or_equal' => 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai.',
            'events.*.parsed_dates.dates.*.end.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'events.*.location_data.*.location_id.required' => 'Lokasi acara wajib dipilih.',
            'events.*.location_data.*.location_id.exists' => 'Lokasi acara tidak valid.',
            'events.*.location_data.*.name.required' => 'Nama lokasi acara wajib diisi.',
            'events.*.location_data.*.source.required' => 'Sumber lokasi acara wajib dipilih.',
            'events.*.location_data.*.source.in' => 'Sumber lokasi acara tidak valid.',

            'events.*.schedule.*.description.required' => 'Deskripsi jadwal wajib diisi.',
            'events.*.schedule.*.startTime_processed.time.required' => 'Waktu mulai jadwal wajib diisi.',
            'events.*.schedule.*.startTime_processed.time.date_format' => 'Format waktu mulai jadwal tidak valid (HH:MM).',
            'events.*.schedule.*.startTime_processed.time.before_or_equal' => 'Waktu mulai jadwal harus sebelum atau sama dengan waktu selesai.',
            'events.*.schedule.*.endTime_processed.time.required' => 'Waktu selesai jadwal wajib diisi.',
            'events.*.schedule.*.endTime_processed.time.date_format' => 'Format waktu selesai jadwal tidak valid (HH:MM).',
            'events.*.schedule.*.endTime_processed.time.after_or_equal' => 'Waktu selesai jadwal harus setelah atau sama dengan waktu mulai.',

            'events.*.equipment.*.quantity.required' => 'Jumlah peralatan wajib diisi.',
            'events.*.equipment.*.quantity.integer' => 'Jumlah peralatan harus berupa angka.',
            'events.*.equipment.*.quantity.min' => 'Jumlah peralatan minimal 1.',
            'events.*.equipment.*.item_id.required' => 'Peralatan wajib dipilih.',
            'events.*.equipment.*.item_id.exists' => 'Peralatan tidak valid.',
        ];
    }

    public function setData(array $data): void
    {
        $this->id = data_get($data, 'id');
        $this->type = data_get($data, 'type');
        $this->document_information = data_get($data, 'document_information', []);
        $this->events = data_get($data, 'events', []);
        $this->signature_blocks = data_get($data, 'signature_blocks', []);
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'document_information' => $this->document_information,
            'events' => $this->events,
            'signature_blocks' => $this->signature_blocks,
        ];
    }
}
