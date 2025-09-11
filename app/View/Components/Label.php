<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LibreNMS\Enum\Severity;

class Label extends Component
{
    public string $statusClass;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?Severity $status = null
    ) {
        $this->statusClass = match ($status) {
            Severity::Ok => 'label-success',
            Severity::Error => 'label-danger',
            Severity::Info => 'label-info',
            Severity::Notice => 'label-primary',
            Severity::Warning => 'label-warning',
            default => 'label-default',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.label');
    }
}
