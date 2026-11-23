<?php

namespace App\Graphs\Application;

class PoudriereInvoluntaryContextSwitchesGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'involuntary-context-switches' => __('graph.application.poudriere.involuntary_context'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.switches');
    }
}
