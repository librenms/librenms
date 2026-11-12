<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class DiskGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric('vm.disk');

        return [
            'used' => new DataSeries($metric, 'used', 'Used Disk', 'B', style: new SeriesStyle(color: 'CC0000', fill: true)),
            'total' => new DataSeries($metric, 'total', 'Total Disk', 'B', style: new SeriesStyle(color: '708090')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, base: 1024, legendTitle: 'Disk');
    }
}
