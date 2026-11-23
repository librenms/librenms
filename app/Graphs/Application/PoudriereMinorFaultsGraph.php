<?php

namespace App\Graphs\Application;

class PoudriereMinorFaultsGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'minor-faults' => __('graph.application.poudriere.minor_faults'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.faults');
    }
}
