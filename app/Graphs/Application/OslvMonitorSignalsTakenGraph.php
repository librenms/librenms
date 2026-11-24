<?php

namespace App\Graphs\Application;

class OslvMonitorSignalsTakenGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'signals-taken' => __('graph.application.oslv_monitor.signals'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.signals_per_second');
    }
}
