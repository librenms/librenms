<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReqTraceGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'req_trace' => new DataSeries($this->metric(), 'req_trace', __('graph.application.privoxy.req_trace'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.req_trace'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
