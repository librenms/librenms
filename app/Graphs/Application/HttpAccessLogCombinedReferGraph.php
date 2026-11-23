<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedReferGraph extends HttpAccessLogCombinedGraph
{
    protected function stats(): array
    {
        return [
            'refer' => __('graph.application.http_access_log_combined.refer_present'),
            'no_refer' => __('graph.application.http_access_log_combined.refer_not_present'),
        ];
    }
}
