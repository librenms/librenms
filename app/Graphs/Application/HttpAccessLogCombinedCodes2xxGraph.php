<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedCodes2xxGraph extends HttpAccessLogCombinedGraph
{
    protected const string PALETTE = 'rainbow';

    protected function stats(): array
    {
        // status codes are their own labels
        $codes = [
            '200', '201', '202', '203', '204', '205', '206', '207', '208', '218', '226',
        ];

        return array_combine($codes, $codes);
    }
}
