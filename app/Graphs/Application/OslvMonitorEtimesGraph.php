<?php

namespace App\Graphs\Application;

class OslvMonitorEtimesGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'elapsed-times' => __('graph.application.oslv_monitor.elapsed_time'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.seconds');
    }
}
