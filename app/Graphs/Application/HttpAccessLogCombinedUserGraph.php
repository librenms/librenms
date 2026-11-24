<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedUserGraph extends HttpAccessLogCombinedGraph
{
    protected function stats(): array
    {
        return [
            'user' => __('graph.application.http_access_log_combined.user_present'),
            'no_user' => __('graph.application.http_access_log_combined.user_not_present'),
        ];
    }
}
