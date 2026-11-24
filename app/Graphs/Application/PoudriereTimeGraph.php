<?php

namespace App\Graphs\Application;

class PoudriereTimeGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'TIME' => 'TIME',
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.seconds');
    }
}
