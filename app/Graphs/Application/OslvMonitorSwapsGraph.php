<?php

namespace App\Graphs\Application;

class OslvMonitorSwapsGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'swaps' => __('graph.application.oslv_monitor.swaps'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.swaps_per_second');
    }
}
