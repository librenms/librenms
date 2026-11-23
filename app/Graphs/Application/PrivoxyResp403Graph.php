<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyResp403Graph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'resp_403' => new DataSeries($this->metric(), 'resp_403', __('graph.application.privoxy.resp_403'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.resp_403'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
