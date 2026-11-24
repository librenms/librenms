<?php

namespace App\Graphs\Application;

class OslvMonitorCpuPercentGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'percent-cpu' => __('graph.application.oslv_monitor.cpu_percent'),
        ];
    }

    protected function legendTitle(): string
    {
        return '';
    }
}
