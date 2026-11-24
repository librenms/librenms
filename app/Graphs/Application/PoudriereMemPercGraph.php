<?php

namespace App\Graphs\Application;

class PoudriereMemPercGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'percent-memory' => __('graph.application.poudriere.mem_percent'),
        ];
    }

    protected function legendTitle(): string
    {
        return '';
    }
}
