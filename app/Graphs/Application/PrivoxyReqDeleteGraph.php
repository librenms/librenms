<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReqDeleteGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'req_delete' => new DataSeries($this->metric(), 'req_delete', __('graph.application.privoxy.req_delete'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.req_delete'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
