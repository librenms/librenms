<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyClientRequestsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'client_requests' => new DataSeries($this->metric(), 'client_requests', __('graph.application.privoxy.client_requests'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.client_requests'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
