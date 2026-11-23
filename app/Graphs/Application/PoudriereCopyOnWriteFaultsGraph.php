<?php

namespace App\Graphs\Application;

class PoudriereCopyOnWriteFaultsGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'copy-on-write-faults' => __('graph.application.poudriere.cow_faults'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.faults');
    }
}
