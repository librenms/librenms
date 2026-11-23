<?php

namespace App\Graphs\Application;

class OslvMonitorThrottledCountGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'nr_throttled' => __('graph.application.oslv_monitor.throttled'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.events_per_second');
    }
}
