<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyResp3xxOtherGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'resp_3xx_other' => new DataSeries($this->metric(), 'resp_3xx_other', __('graph.application.privoxy.resp_3xx_other'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.resp_3xx_other'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
