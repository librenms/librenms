<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class IcecastMemoryusageGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'kbyte' => new DataSeries($this->metric(), 'kbyte', __('graph.units.bytes'),
                style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 1000, legendTitle: __('graph.units.bytes'));
    }
}
