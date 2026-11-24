<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ApacheBitsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'kbyte' => new DataSeries($this->metric(), 'kbyte', __('graph.units.kilobits_per_second'), scale: 8,
                style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.kilobits_per_second'));
    }
}
