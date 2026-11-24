<?php

namespace App\Graphs\Application;

class OslvMonitorCowsGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'copy-on-write-faults' => __('graph.application.oslv_monitor.cow_faults'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.application.oslv_monitor.cows_per_sec');
    }
}
