<?php

namespace App\Graphs\Application;

class OslvMonitorBurstTimeGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'burst-time' => __('graph.application.oslv_monitor.burst_time'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.seconds_per_second');
    }
}
