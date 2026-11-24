<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class ApacheCpuGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'cpu' => new DataSeries($this->metric(), 'cpu', __('graph.units.percent_used'),
                style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.percent_used'));
    }
}
