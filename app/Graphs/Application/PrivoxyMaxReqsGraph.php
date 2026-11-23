<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyMaxReqsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'max_reqs' => new DataSeries($this->metric(), 'max_reqs', __('graph.application.privoxy.max_reqs'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.max_reqs'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
