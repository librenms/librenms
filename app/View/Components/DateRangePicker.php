<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DateRangePicker extends Component
{
    public string $componentId;
    /** @var array array */
    public array $availablePresets;


    public function __construct(
        public string $name = 'date_range',
        public string $value = '',
        public string $placeholder = 'Select date range...',
        public bool $required = false,
        public bool $disabled = false,
        public string $class = 'tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-md',
        public bool $presets = true
    ) {
        $this->componentId = 'date-range-' . uniqid();
        $this->availablePresets = $this->getPresets();
    }

    protected function getPresets(): array
    {
        return [
            '6h' => [
                'label' => '6h',
                'text' => 'Last 6 hours',
                'hours' => 6,
            ],
            '24h' => [
                'label' => '24h',
                'text' => 'Last 24 hours',
                'hours' => 24,
            ],
            '48h' => [
                'label' => '48h',
                'text' => 'Last 48 hours',
                'hours' => 48,
            ],
            '1w' => [
                'label' => '1w',
                'text' => 'Last week',
                'days' => 7,
            ],
            '2w' => [
                'label' => '2w',
                'text' => 'Last 2 weeks',
                'days' => 14,
            ],
            '1m' => [
                'label' => '1m',
                'text' => 'Last month',
                'days' => 30,
            ],
            '2m' => [
                'label' => '2m',
                'text' => 'Last 2 months',
                'days' => 60,
            ],
            '1y' => [
                'label' => '1y',
                'text' => 'Last year',
                'days' => 365,
            ],
            '2y' => [
                'label' => '2y',
                'text' => 'Last 2 years',
                'days' => 730,
            ],
        ];
    }

    public function render(): View
    {
        return view('components.date-range-picker');
    }
}
