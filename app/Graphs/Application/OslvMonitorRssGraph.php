<?php

namespace App\Graphs\Application;

class OslvMonitorRssGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'rss' => __('graph.application.oslv_monitor.rss'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.kilobytes');
    }
}
