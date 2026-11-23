<?php

namespace App\Graphs\Application;

class OslvMonitorCgroupsThpActivityGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'thp_fault_alloc' => 'thp_fault_alloc',
            'thp_collapse_alloc' => 'thp_collapse_alloc',
            'thp_swpout' => 'thp_swpout',
            'thp_swpout_fallback' => 'thp_swpout_fallback',
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.per_second');
    }
}
