<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DateRangePicker extends Component
{
    /** @var array array */
    public array $availablePresets;

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
        $this->availablePresets = $this->getPresets();
    }

    protected function getPresets(): array
    {
        return [
            '6h' => [
                'label' => '6h',
                'text' => 'Last 6 hours',
                'seconds' => 6 * 60 * 60,
            ],
            '24h' => [
                'label' => '24h',
                'text' => 'Last 24 hours',
                'seconds' => 24 * 60 * 60,
            ],
            '48h' => [
                'label' => '48h',
                'text' => 'Last 48 hours',
                'seconds' => 48 * 60 * 60,
            ],
            '1w' => [
                'label' => '1w',
                'text' => 'Last week',
                'seconds' => 7 * 24 * 60 * 60,
            ],
            '2w' => [
                'label' => '2w',
                'text' => 'Last 2 weeks',
                'seconds' => 14 * 24 * 60 * 60,
            ],
            '1m' => [
                'label' => '1m',
                'text' => 'Last month',
                'seconds' => 30 * 24 * 60 * 60,
            ],
            '2m' => [
                'label' => '2m',
                'text' => 'Last 2 months',
                'seconds' => 60 * 24 * 60 * 60,
            ],
            '1y' => [
                'label' => '1y',
                'text' => 'Last year',
                'seconds' => 365 * 24 * 60 * 60,
            ],
            '2y' => [
                'label' => '2y',
                'text' => 'Last 2 years',
                'seconds' => 730 * 24 * 60 * 60,
            ],
        ];
    }

    public function render(): View
    {
        return view('components.date-range-picker');
    }
}
