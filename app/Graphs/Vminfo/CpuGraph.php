<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class CpuGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        return [
            'usage' => new DataSeries($this->metric('vm.cpu'), 'usage', 'CPU Usage', '%', style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, scaleMax: 100, legendTitle: 'CPU Usage %', palette: 'oranges');
    }
}
