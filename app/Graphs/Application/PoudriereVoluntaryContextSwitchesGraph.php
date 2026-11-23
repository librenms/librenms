<?php

namespace App\Graphs\Application;

class PoudriereVoluntaryContextSwitchesGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'voluntary-context-switches' => __('graph.application.poudriere.voluntary_context'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.switches');
    }
}
