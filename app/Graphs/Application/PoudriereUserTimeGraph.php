<?php

namespace App\Graphs\Application;

class PoudriereUserTimeGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'user-time' => __('graph.application.poudriere.user_time'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.seconds');
    }
}
