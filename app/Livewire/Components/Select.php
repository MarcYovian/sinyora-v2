<?php

namespace App\Livewire\Components;

use Illuminate\Support\Collection;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Select extends Component
{
    #[Modelable]
    public $selected = [];

    // Konfigurasi komponen
    public string $model;
    public string $displayColumn;
    public string $searchColumn;

    // State internal komponen
    public string $search = '';
    public Collection $options;
    public bool $showDropdown = false;

    public function mount(string $model, string $displayColumn, string $searchColumn = null, array $initialSelected = [])
    {
        $this->model = $model;
        $this->displayColumn = $displayColumn;
        $this->searchColumn = $searchColumn ?? $displayColumn;
        $this->options = collect();

        if (!empty($initialSelected)) {
            $this->initializeSelected($initialSelected);
        }
    }

    private function initializeSelected(array $initialSelected): void
    {
        if (empty($initialSelected)) {
            $this->selected = [];
            return;
        }

        // Jika data sudah dalam format yang benar (array of arrays)
        if (is_array($initialSelected[0] ?? null)) {
            $this->selected = $initialSelected;
            return;
        }

        // Jika data masih berupa array ID, konversi ke format yang benar
        $models = app($this->model)::whereIn('id', $initialSelected)->get();
        $this->selected = $models->map(function ($item) {
            return [
                'id' => $item->id,
                $this->displayColumn => $item->{$this->displayColumn},
            ];
        })->toArray();
    }

    public function updatedSelected(): void
    {
        $this->normalizeSelected();
    }

    private function normalizeSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Jika ada item yang bukan array (hanya ID), konversi ke format yang benar
        $needsNormalization = false;
        foreach ($this->selected as $item) {
            if (!is_array($item)) {
                $needsNormalization = true;
                break;
            }
        }

        if ($needsNormalization) {
            $ids = collect($this->selected)->filter(fn($item) => !is_array($item))->values()->toArray();
            $existingArrays = collect($this->selected)->filter(fn($item) => is_array($item))->values()->toArray();

            if (!empty($ids)) {
                $models = app($this->model)::whereIn('id', $ids)->get();
                $newArrays = $models->map(function ($item) {
                    return [
                        'id' => $item->id,
                        $this->displayColumn => $item->{$this->displayColumn},
                    ];
                })->toArray();

                $this->selected = array_merge($existingArrays, $newArrays);
            } else {
                $this->selected = $existingArrays;
            }
        }
    }


    public function updatedSearch()
    {
        $this->showDropdown = true;
        if (empty($this->search)) {
            $this->options = collect();
            return;
        }
        $this->options = app($this->model)::where($this->searchColumn, 'like', '%' . $this->search . '%')
            ->limit(5)
            ->get(['id', $this->displayColumn]);
    }

    public function selectItem($id)
    {
        $item = app($this->model)::find($id);
        if ($item && !$this->isSelected($item->id)) {
            $this->selected[] = [
                'id' => $item->id,
                $this->displayColumn => $item->{$this->displayColumn},
            ];
        }
        $this->resetSearch();
    }

    public function addNewTag()
    {
        if (empty($this->search)) {
            return;
        }
        $existing = app($this->model)::where($this->displayColumn, $this->search)->first();
        if ($existing) {
            $this->selectItem($existing->id);
            return;
        }
        $newItem = [
            'id' => 'new:' . time(),
            $this->displayColumn => $this->search
        ];
        if (!$this->isSelectedByName($this->search)) {
            $this->selected[] = $newItem;
        }
        $this->resetSearch();
    }

    public function removeItem($id)
    {
        $this->normalizeSelected();

        $this->selected = array_values(array_filter($this->selected, function ($item) use ($id) {
            // Pastikan $item adalah array dan memiliki key 'id'
            return is_array($item) && isset($item['id']) && $item['id'] != $id;
        }));
    }

    private function isSelected($id): bool
    {
        $this->normalizeSelected();
        $validItems = array_filter($this->selected, 'is_array');
        return in_array($id, array_column($validItems, 'id'));
    }

    private function isSelectedByName(string $name): bool
    {
        $this->normalizeSelected();
        $validItems = array_filter($this->selected, 'is_array');
        return in_array($name, array_column($validItems, $this->displayColumn));
    }

    public function resetSearch()
    {
        $this->search = '';
        $this->options = collect();
        $this->showDropdown = false;
    }

    public function rendering()
    {
        $this->normalizeSelected();
    }

    public function render()
    {
        return view('livewire.components.select');
    }
}
