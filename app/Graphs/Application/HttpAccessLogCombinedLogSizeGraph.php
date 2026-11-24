<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedLogSizeGraph extends HttpAccessLogCombinedGraph
{
    protected const string LEGEND = 'graph.units.bytes';

    protected function stats(): array
    {
        return ['size' => __('graph.application.http_access_log_combined.log_size')];
    }
}
