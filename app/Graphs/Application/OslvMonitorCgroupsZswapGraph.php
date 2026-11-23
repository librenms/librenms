<?php

namespace App\Graphs\Application;

class OslvMonitorCgroupsZswapGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'zswap' => __('graph.application.oslv_monitor.zswap_size'),
            'zswapped' => __('graph.application.oslv_monitor.zswapped_size'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.bytes');
    }
}
