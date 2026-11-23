<?php

namespace App\Graphs\Application;

class PoudriereDataSizeGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'data-size' => __('graph.application.poudriere.data_size'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.kilobytes');
    }
}
