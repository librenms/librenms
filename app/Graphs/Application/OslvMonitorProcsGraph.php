<?php

namespace App\Graphs\Application;

class OslvMonitorProcsGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'procs' => __('graph.application.oslv_monitor.processes'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.count');
    }
}
