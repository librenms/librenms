<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReqGetGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'req_get' => new DataSeries($this->metric(), 'req_get', __('graph.application.privoxy.req_get'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.req_get'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
