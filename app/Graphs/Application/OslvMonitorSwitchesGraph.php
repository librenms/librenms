<?php

namespace App\Graphs\Application;

class OslvMonitorSwitchesGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'voluntary-context-switches' => __('graph.application.oslv_monitor.voluntary'),
            'involuntary-context-switches' => __('graph.application.oslv_monitor.involuntary'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.switches_per_second');
    }
}
