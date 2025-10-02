<?php

namespace App\Livewire\Forms;

use App\Models\MassSchedule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class MassScheduleForm extends Form
{
    public ?MassSchedule $massSchedule = null;

    #[Validate('required|string|max:255')]
    public string $label = '';
    #[Validate('required|integer|between:0,6')]
    public int $day_of_week = 0;
    #[Validate('required|date_format:H:i')]
    public string $start_time = '';
    #[Validate('nullable|string|max:255')]
    public string $description = '';

    public function setMassSchedule(?MassSchedule $massSchedule)
    {
        $this->massSchedule = $massSchedule;
        $this->label = $massSchedule->label;
        $this->day_of_week = $massSchedule->day_of_week;
        $this->start_time = $massSchedule->start_time;
        $this->description = $massSchedule->description;
    }
}
