<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Html;

class Label extends Component
{
    public string $statusClass;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?Severity $status = null
    ) {
        $this->statusClass = Html::severityClass($status ?? Severity::Unknown);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.label');
    }
}
