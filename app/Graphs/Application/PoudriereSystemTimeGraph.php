<?php

namespace App\Graphs\Application;

class PoudriereSystemTimeGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'system-time' => __('graph.application.poudriere.system_time'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.seconds');
    }
}
