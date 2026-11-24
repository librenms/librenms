<?php

namespace App\Graphs\Application;

class PoudriereReadBlocksGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'read-blocks' => __('graph.application.poudriere.read_blocks'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.blocks');
    }
}
