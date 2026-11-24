<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedErrorSizeGraph extends HttpAccessLogCombinedGraph
{
    protected const string LEGEND = 'graph.units.bytes';

    protected function stats(): array
    {
        return ['error_size' => __('graph.application.http_access_log_combined.err_log_size')];
    }
}
