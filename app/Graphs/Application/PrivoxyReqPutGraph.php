<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReqPutGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'req_put' => new DataSeries($this->metric(), 'req_put', __('graph.application.privoxy.req_put'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.req_put'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
