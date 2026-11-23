<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyResp503Graph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'resp_503' => new DataSeries($this->metric(), 'resp_503', __('graph.application.privoxy.resp_503'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.resp_503'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
