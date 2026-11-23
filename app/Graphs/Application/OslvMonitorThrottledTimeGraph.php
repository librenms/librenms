<?php

namespace App\Graphs\Application;

class OslvMonitorThrottledTimeGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'throttled-time' => __('graph.application.oslv_monitor.throttled_time'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.seconds_per_second');
    }
}
