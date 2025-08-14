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
    public function __construct(?EventApprovalStatus $status)
    {
        [$this->label, $this->colorClasses] = match ($status) {
            EventApprovalStatus::APPROVED => [
                __('Approved'),
                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
            ],
            EventApprovalStatus::PENDING => [
                __('Pending'),
                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
            ],
            EventApprovalStatus::REJECTED => [
                __('Rejected'),
                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
            ],
            // Default case jika status tidak dikenali atau null
            default => [
                __('Unknown'),
                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
            ],
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.status-badge');
    }
}
