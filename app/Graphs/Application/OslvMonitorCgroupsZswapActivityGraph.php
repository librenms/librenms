<?php

namespace App\Graphs\Application;

class OslvMonitorCgroupsZswapActivityGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'zswpin' => 'zswpin',
            'zswpout' => 'zswpout',
            'zswpwb' => 'zswpwb',
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.kilobytes');
    }
}
