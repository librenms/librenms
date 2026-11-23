<?php

namespace App\Graphs\Application;

class PoudriereMajorFaultsGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'major-faults' => __('graph.application.poudriere.major_faults'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.faults');
    }
}
