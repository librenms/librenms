<?php

namespace App\Graphs\Application;

class OslvMonitorMemPercentGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'percent-memory' => __('graph.application.oslv_monitor.memory_percent'),
        ];
    }

    protected function legendTitle(): string
    {
        return '';
    }
}
