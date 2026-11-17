<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class MemGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric('vm.memory');

        return [
            'used' => new DataSeries($metric, 'used', 'Used Memory', 'B', style: new SeriesStyle(color: 'CC0000', fill: true)),
            'total' => new DataSeries($metric, 'total', 'Total Memory', 'B', style: new SeriesStyle(color: '708090')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, base: 1024, legendTitle: 'Memory');
    }
}
