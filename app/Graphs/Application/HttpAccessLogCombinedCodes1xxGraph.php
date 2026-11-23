<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedCodes1xxGraph extends HttpAccessLogCombinedGraph
{
    protected function stats(): array
    {
        // status codes are their own labels
        $codes = [
            '100', '101', '102', '103',
        ];

        return array_combine($codes, $codes);
    }
}
