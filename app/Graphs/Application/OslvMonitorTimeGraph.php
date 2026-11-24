<?php

namespace App\Graphs\Application;

class OslvMonitorTimeGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'cpu-time' => __('graph.application.oslv_monitor.cpu'),
            'system-time' => __('graph.application.oslv_monitor.system'),
            'user-time' => __('graph.application.oslv_monitor.user'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.seconds_per_second');
    }
}
