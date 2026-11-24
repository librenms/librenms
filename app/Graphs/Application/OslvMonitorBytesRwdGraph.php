<?php

namespace App\Graphs\Application;

class OslvMonitorBytesRwdGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'rbytes' => __('graph.series.read'),
            'wbytes' => __('graph.series.write'),
            'dbytes' => __('graph.series.discard'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.bytes_per_second');
    }
}
