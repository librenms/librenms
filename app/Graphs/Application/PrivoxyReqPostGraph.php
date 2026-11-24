<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReqPostGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'req_post' => new DataSeries($this->metric(), 'req_post', __('graph.application.privoxy.req_post'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.req_post'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
