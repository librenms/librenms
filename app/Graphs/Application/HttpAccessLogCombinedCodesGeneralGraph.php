<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedCodesGeneralGraph extends HttpAccessLogCombinedGraph
{
    protected function stats(): array
    {
        // status codes are their own labels
        $codes = [
            '1xx', '2xx', '3xx', '4xx', '5xx',
        ];

        return array_combine($codes, $codes);
    }
}
