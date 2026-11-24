<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedBytesGraph extends HttpAccessLogCombinedGraph
{
    protected const string LEGEND = 'graph.units.bytes';

    protected function stats(): array
    {
        return ['bytes' => __('graph.units.bytes')];
    }
}
