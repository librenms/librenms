<?php

namespace App\Graphs\Application;

class PoudriereContextSwitchesComparisonGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'voluntary-context-switches' => __('graph.application.poudriere.voluntary_context'),
            'involuntary-context-switches' => __('graph.application.poudriere.involuntary_context'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.switches');
    }
}
