<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class DiskioOpsGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric('vm.diskops');

        return [
            'read' => new DataSeries($metric, 'read_ops', 'Read', style: new SeriesStyle(color: '75BA30', fill: true, fillColor: 'CAE853')),
            'write' => new DataSeries($metric, 'write_ops', 'Write', style: new SeriesStyle(color: '6A6AB7', fill: true, fillColor: 'A9A9F2', invert: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: 'Ops/sec');
    }
}
