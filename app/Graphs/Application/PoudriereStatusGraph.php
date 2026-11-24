<?php

namespace App\Graphs\Application;

class PoudriereStatusGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'QUEUE' => 'QUEUE',
            'BUILT' => 'BUILT',
            'FAIL' => 'FAIL',
            'SKIP' => 'SKIP',
            'IGNORE' => 'IGNORE',
            'REMAIN' => 'REMAIN',
            'FETCH' => 'FETCH',
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.count');
    }
}
