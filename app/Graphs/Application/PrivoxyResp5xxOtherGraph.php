<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyResp5xxOtherGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'resp_5xx_other' => new DataSeries($this->metric(), 'resp_5xx_other', __('graph.application.privoxy.resp_5xx_other'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.resp_5xx_other'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
