<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\SeriesStyle;

class DiskioBitsGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric('vm.diskio');

        return [
            'read' => new DataSeries($metric, 'read_bytes', 'Read', 'bps', 8, new SeriesStyle(color: '75BA30', fill: true, fillColor: 'CAE853')),
            'write' => new DataSeries($metric, 'write_bytes', 'Write', 'bps', 8, new SeriesStyle(color: '6A6AB7', fill: true, fillColor: 'A9A9F2', invert: true)),
        ];
    }
}
