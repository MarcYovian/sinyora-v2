<?php

namespace App\View\Components;

use App\Enums\EventApprovalStatus;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatusBadge extends Component
{
    public string $label;
    public string $colorClasses;
    /**
     * Create a new component instance.
     */
    public function __construct(object $status = null)
    {
        if ($status && method_exists($status, 'label') && method_exists($status, 'color')) {
            // Jika objek status valid dan memiliki method yang dibutuhkan, panggil method tersebut.
            $this->label = $status->label();
            $this->colorClasses = $status->color();
        } else {
            // Fallback jika status null atau tidak memiliki method yang diharapkan.
            $this->label = __('Unknown');
            $this->colorClasses = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.status-badge');
    }
}
