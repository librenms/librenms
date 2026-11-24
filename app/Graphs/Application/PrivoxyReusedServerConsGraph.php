<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyReusedServerConsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'reused_server_cons' => new DataSeries($this->metric(), 'reused_server_cons', __('graph.application.privoxy.reused_server_cons'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.reused_server_cons'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
