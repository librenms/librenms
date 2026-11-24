<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyNogConnsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'nog_conns' => new DataSeries($this->metric(), 'nog_conns', __('graph.application.privoxy.nog_conns'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.nog_conns'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
