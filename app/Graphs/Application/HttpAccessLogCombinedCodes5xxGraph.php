<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedCodes5xxGraph extends HttpAccessLogCombinedGraph
{
    protected const string PALETTE = 'rainbow';

    protected function stats(): array
    {
        // status codes are their own labels
        $codes = [
            '500', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '511',
        ];

        return array_combine($codes, $codes);
    }
}
