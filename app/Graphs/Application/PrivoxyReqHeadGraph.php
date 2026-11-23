<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReqHeadGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'req_head' => new DataSeries($this->metric(), 'req_head', __('graph.application.privoxy.req_head'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.req_head'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
