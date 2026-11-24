<?php

namespace App\Graphs\Application;

class PoudriereCpuPercGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'percent-cpu' => __('graph.application.poudriere.cpu_percent'),
        ];
    }

    protected function legendTitle(): string
    {
        return '';
    }
}
