<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;

class DiskioOpsGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric('vm.diskops');

        return [
            'read' => new DataSeries($metric, 'read_ops', __('graph.series.read'), style: new SeriesStyle(SeriesRole::In, fill: true)),
            'write' => new DataSeries($metric, 'write_ops', __('graph.series.write'), style: new SeriesStyle(SeriesRole::Out, fill: true, invert: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.vminfo.ops'));
    }
}
