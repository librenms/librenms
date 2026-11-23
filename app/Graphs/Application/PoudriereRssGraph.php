<?php

namespace App\Graphs\Application;

class PoudriereRssGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'rss' => __('graph.application.poudriere.rss'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.kilobytes');
    }
}
