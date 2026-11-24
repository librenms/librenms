<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedCodes4xxGraph extends HttpAccessLogCombinedGraph
{
    protected function stats(): array
    {
        // status codes are their own labels
        $codes = [
            '400', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411',
            '412', '413', '414', '415', '416', '417', '419', '420', '421', '422', '423', '424',
            '425', '426', '428', '429', '431', '444', '451', '494', '495', '496', '497', '499',
        ];

        return array_combine($codes, $codes);
    }
}
