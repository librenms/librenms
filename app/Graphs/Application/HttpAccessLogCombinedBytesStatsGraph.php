<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedBytesStatsGraph extends HttpAccessLogCombinedGraph
{
    protected const string LEGEND = 'graph.units.bytes';

    protected function stats(): array
    {
        return [
            'bytes_mean' => __('graph.series.mean'),
            'bytes_median' => __('graph.series.median'),
            'bytes_mode' => __('graph.series.mode'),
            'bytes_max' => __('graph.series.max'),
            'bytes_min' => __('graph.series.min'),
        ];
    }
}
