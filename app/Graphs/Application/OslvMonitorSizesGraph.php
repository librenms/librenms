<?php

namespace App\Graphs\Application;

class OslvMonitorSizesGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'data-size' => __('graph.application.oslv_monitor.data'),
            'stack-size' => __('graph.application.oslv_monitor.stack'),
            'text-size' => __('graph.application.oslv_monitor.text'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.kilobytes');
    }
}
