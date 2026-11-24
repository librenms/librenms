<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyOutRequestsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'out_requests' => new DataSeries($this->metric(), 'out_requests', __('graph.application.privoxy.out_requests'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.out_requests'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
