<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ApacheHitsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'access' => new DataSeries($this->metric(), 'access', __('graph.units.hits_per_second'),
                style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.hits_per_second'));
    }
}
