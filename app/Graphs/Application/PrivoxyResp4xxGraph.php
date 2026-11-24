<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyResp4xxGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'resp_4xx' => new DataSeries($this->metric(), 'resp_4xx', __('graph.application.privoxy.resp_4xx'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.resp_4xx'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
