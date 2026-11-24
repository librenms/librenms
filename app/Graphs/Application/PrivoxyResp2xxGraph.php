<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyResp2xxGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'resp_2xx' => new DataSeries($this->metric(), 'resp_2xx', __('graph.application.privoxy.resp_2xx'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.resp_2xx'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
