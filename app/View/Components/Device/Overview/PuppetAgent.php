<?php

namespace App\View\Components\Device\Overview;

use App\Models\Application;
use App\Models\ApplicationMetric;
use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class PuppetAgent extends Component
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, ApplicationMetric>
     */
    public Collection $metrics;

    public function __construct(
        public Device $device,
        public ?Application $application,
    ) {
        $this->metrics = $application?->metrics->pluck('value', 'metric') ?? collect();
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.puppet-agent');
    }
}
