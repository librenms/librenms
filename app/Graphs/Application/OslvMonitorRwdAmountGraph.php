<?php

namespace App\Graphs\Application;

class OslvMonitorRwdAmountGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'rbytes' => __('graph.series.read'),
            'wbytes' => __('graph.series.write'),
            'dbytes' => __('graph.series.discard'),
            'read-blocks' => __('graph.series.read'),
            'written-blocks' => __('graph.series.written'),
        ];
    }

    protected function legendTitle(): string
    {
        // cgroups counts bytes, the other backends count blocks
        return ($this->application()->data['backend'] ?? null) === 'cgroups'
            ? __('graph.units.bytes_per_second')
            : __('graph.units.blocks_per_second');
    }
}
