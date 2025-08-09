<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DateRangePicker extends Component
{
    public function __construct(
        public string $name = 'date_range',
        public string $start = '',
        public string $end = '',
        public string $value = '',
        public string $placeholder = 'Select date range...',
        public bool $required = false,
        public bool $disabled = false,
        public string $class = 'tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-md',
        public bool $presets = true
    ) {
    }

    public function render(): View
    {
        return view('components.date-range-picker');
    }
}
