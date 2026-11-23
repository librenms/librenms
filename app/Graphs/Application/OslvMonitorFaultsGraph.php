<?php

namespace App\Graphs\Application;

class OslvMonitorFaultsGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'minor-faults' => __('graph.application.oslv_monitor.minor'),
            'major-faults' => __('graph.application.oslv_monitor.major'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.faults_per_second');
    }
}
