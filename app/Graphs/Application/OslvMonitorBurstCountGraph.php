<?php

namespace App\Graphs\Application;

class OslvMonitorBurstCountGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'nr_bursts' => __('graph.application.oslv_monitor.bursts'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.events_per_second');
    }
}
