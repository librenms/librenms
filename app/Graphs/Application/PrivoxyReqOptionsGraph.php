<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReqOptionsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'req_options' => new DataSeries($this->metric(), 'req_options', __('graph.application.privoxy.req_options'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.req_options'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
