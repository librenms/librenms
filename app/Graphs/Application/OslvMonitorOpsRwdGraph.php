<?php

namespace App\Graphs\Application;

class OslvMonitorOpsRwdGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'rios' => __('graph.series.read'),
            'wios' => __('graph.series.write'),
            'dios' => __('graph.series.discard'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.ops_per_second');
    }
}
