<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedCodes3xxGraph extends HttpAccessLogCombinedGraph
{
    protected const string PALETTE = 'rainbow';

    protected function stats(): array
    {
        // status codes are their own labels
        $codes = [
            '300', '301', '302', '303', '304', '305', '306', '307', '308',
        ];

        return array_combine($codes, $codes);
    }
}
