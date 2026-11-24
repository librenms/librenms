<?php

namespace App\Graphs\Application;

class OslvMonitorBlocksGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'read-blocks' => __('graph.series.read'),
            'written-blocks' => __('graph.series.written'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.blocks_per_second');
    }
}
