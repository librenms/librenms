<?php

namespace App\Graphs\Application;

class PoudriereStackSizeGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'stack-size' => __('graph.application.poudriere.stack_size'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.kilobytes');
    }
}
