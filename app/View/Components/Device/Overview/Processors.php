<?php

namespace App\View\Components\Device\Overview;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Processor;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Processors extends Component
{
    /**
     * @var Collection<(int|string), array{processors: Collection<int, Processor>, usage: int, warning: float|int|null}>
     */
    public Collection $processorGroups;

    public bool $showDetails;

    public function __construct(public Device $device)
    {
        $this->showDetails = LibrenmsConfig::get('cpu_details_overview') === true;
        $this->processorGroups = $device->processors
            ->groupBy('processor_type')
            ->map(fn (Collection $processors): array => [
                'processors' => $processors
                    ->map(fn (Processor $processor): Processor => $processor)
                    ->values(),
                'usage' => (int) ceil($processors->avg('processor_usage')),
                'warning' => $processors->avg('processor_perc_warn'),
            ],
            );
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.processors');
    }
}
