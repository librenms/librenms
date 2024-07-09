<?php

namespace App\View\Components;

use App\Models\Transceiver;
use App\Models\TransceiverMetric;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use LibreNMS\Enum\Severity;

class TransceiverMetrics extends Component
{
    public Collection $groupedMetrics;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Transceiver $transceiver,
    ) {
        $this->groupedMetrics = $transceiver->metrics
            ->groupBy('type');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.transceiver-metrics');
    }

    public function status(Collection $metrics): Severity
    {
        return $metrics->reduce(function (Severity $previous, TransceiverMetric $metric) {
            $current = $metric->status->asSeverity();

            return $current->value > $previous->value ? $current : $previous;
        }, Severity::Unknown);
    }

    public function value(Collection $metrics): string
    {
        $value = $metrics->firstWhere('channel', 0)?->value ?? $metrics->avg('value');

        return round($value, 3) . ' ' . __('port.transceivers.units.' . $metrics->first()->type);
    }
}
