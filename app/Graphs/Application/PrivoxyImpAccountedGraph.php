<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyImpAccountedGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'imp_accounted' => new DataSeries($this->metric(), 'imp_accounted', __('graph.application.privoxy.imp_accounted'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.imp_accounted'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
