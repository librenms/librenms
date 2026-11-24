<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReqPatchGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'req_patch' => new DataSeries($this->metric(), 'req_patch', __('graph.application.privoxy.req_patch'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.req_patch'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
