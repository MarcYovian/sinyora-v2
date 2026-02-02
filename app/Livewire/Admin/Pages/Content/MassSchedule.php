<?php

namespace App\Livewire\Admin\Pages\Content;

use App\Livewire\Forms\MassScheduleForm;
use App\Services\MassScheduleService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class MassSchedule extends Component
{
    #[Layout('layouts.app')]
    #[Title('Manage Mass Schedules')]

    public MassScheduleForm $form;
    public ?int $scheduleIdToDelete = null;

    protected $massScheduleService;

    public function boot(MassScheduleService $massScheduleService)
    {
        $this->massScheduleService = $massScheduleService;
    }

    public function save()
    {
        $this->form->validate();

        if ($this->form->massSchedule) {
            $this->massScheduleService->update($this->form->massSchedule->id, $this->form->all());
            flash()->success('Jadwal Misa berhasil diubah');
        } else {
            $this->massScheduleService->create($this->form->all());
            flash()->success('Jadwal Misa berhasil ditambahkan');
        }

        $this->dispatch('close-modal', 'mass-schedule-modal');
        $this->form->reset();
    }

    public function edit($id)
    {
        $massSchedule = $this->massScheduleService->find($id);
        $this->form->setMassSchedule($massSchedule);
        $this->dispatch('open-modal', 'mass-schedule-modal');
    }

    public function confirmDelete($id)
    {
        $this->scheduleIdToDelete = $id;
        $this->dispatch('open-modal', 'delete-schedule-confirmation');
    }

    public function delete()
    {
        if ($this->scheduleIdToDelete) {
            $this->massScheduleService->delete($this->scheduleIdToDelete);
            flash()->success('Jadwal Misa berhasil dihapus');
            $this->scheduleIdToDelete = null; // Reset ID
            $this->dispatch('close-modal', 'delete-schedule-confirmation');
        }
    }

    public function clear()
    {
        $this->form->reset();
        $this->dispatch('open-modal', 'mass-schedule-modal');
    }

    public function render()
    {
        $table_heads = ['Label', 'Hari', 'Waktu Mulai', 'Deskripsi', ''];
        return view('livewire.admin.pages.content.mass-schedule', [
            'table_heads' => $table_heads,
            'schedules' => $this->massScheduleService->getSchedulesForAdmin(),
        ]);
    }
}
