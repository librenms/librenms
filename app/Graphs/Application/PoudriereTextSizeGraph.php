<?php

namespace App\Graphs\Application;

class PoudriereTextSizeGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'text-size' => __('graph.application.poudriere.text_size'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.kilobytes');
    }
}
