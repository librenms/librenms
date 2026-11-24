<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PrivoxyBlocksGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'blocks' => new DataSeries($this->metric(), 'blocks', __('graph.application.privoxy.blocks'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.privoxy.blocks'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
