<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyResp5xxGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'resp_5xx' => new DataSeries($this->metric(), 'resp_5xx', __('graph.application.privoxy.resp_5xx'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.resp_5xx'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
