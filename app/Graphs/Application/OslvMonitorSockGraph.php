<?php

namespace App\Graphs\Application;

class OslvMonitorSockGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'sock' => 'sock',
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.bytes');
    }
}
