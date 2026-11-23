<?php

namespace App\Graphs\Application;

class PoudriereThreadsGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'threads' => __('graph.application.poudriere.threads'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.count');
    }
}
