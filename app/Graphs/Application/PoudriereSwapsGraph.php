<?php

namespace App\Graphs\Application;

class PoudriereSwapsGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'swaps' => __('graph.application.poudriere.swaps'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.swaps');
    }
}
