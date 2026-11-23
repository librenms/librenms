<?php

namespace App\Graphs\Application;

class PoudriereSizeComparisonGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'stack-size' => __('graph.application.poudriere.stack_size'),
            'data-size' => __('graph.application.poudriere.data_size'),
            'text-size' => __('graph.application.poudriere.text_size'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.kilobytes');
    }
}
