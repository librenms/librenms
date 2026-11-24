<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyResp500Graph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'resp_500' => new DataSeries($this->metric(), 'resp_500', __('graph.application.privoxy.resp_500'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.resp_500'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
