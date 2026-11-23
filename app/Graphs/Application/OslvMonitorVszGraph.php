<?php

namespace App\Graphs\Application;

class OslvMonitorVszGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'virtual-size' => __('graph.application.oslv_monitor.vsz'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.kilobytes');
    }
}
