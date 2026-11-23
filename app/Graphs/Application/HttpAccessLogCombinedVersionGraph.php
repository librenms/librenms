<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedVersionGraph extends HttpAccessLogCombinedGraph
{
    protected function stats(): array
    {
        // HTTP version numbers are not translated
        return [
            'http1_0' => '1.0',
            'http1_1' => '1.1',
            'http2' => '2',
            'http3' => '3',
        ];
    }
}
