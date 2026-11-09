<?php

namespace App\View\Components\Device;

use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FaultTabs extends Component
{
    /** @var array<string, array{text: string, link: string, icon: string}> */
    public array $tabs;

    public function __construct(
        public readonly Device $device,
        public readonly ?string $tab = null,
    ) {
        $this->tabs = [
            'active' => [
                'text' => __('Active'),
                'link' => route('device.faults', $this->device->device_id),
                'icon' => 'fa-exclamation-circle',
            ],
            'history' => [
                'text' => __('Alert History'),
                'link' => route('device.faults.history', $this->device->device_id),
                'icon' => 'fa-history',
            ],
            'stats' => [
                'text' => __('Alert Stats'),
                'link' => route('device.faults.stats', $this->device->device_id),
                'icon' => 'fa-bar-chart',
            ],
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.device.fault-tabs');
    }
}
