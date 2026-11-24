<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyResp451Graph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'resp_451' => new DataSeries($this->metric(), 'resp_451', __('graph.application.privoxy.resp_451'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.resp_451'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
