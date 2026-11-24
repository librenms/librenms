<?php

namespace App\Graphs\Application;

class PoudriereTimeComparisonGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'cpu-time' => __('graph.application.poudriere.cpu_time'),
            'system-time' => __('graph.application.poudriere.system_time'),
            'user-time' => __('graph.application.poudriere.user_time'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.seconds');
    }
}
