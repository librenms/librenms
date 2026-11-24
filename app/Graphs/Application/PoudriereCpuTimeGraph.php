<?php

namespace App\Graphs\Application;

class PoudriereCpuTimeGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'cpu-time' => __('graph.application.poudriere.cpu_time'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.seconds');
    }
}
