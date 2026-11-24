<?php

namespace App\Graphs\Application;

class PoudriereLogSizeGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'log_size_done' => __('graph.application.poudriere.done'),
            'log_size_latest' => __('graph.application.poudriere.latest'),
            'log_size_per_package' => __('graph.application.poudriere.per_package'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.bytes');
    }
}
