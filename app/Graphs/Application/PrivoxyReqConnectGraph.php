<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReqConnectGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'req_connect' => new DataSeries($this->metric(), 'req_connect', __('graph.application.privoxy.req_connect'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.req_connect'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
