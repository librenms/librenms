<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;

class MemGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric('vm.memory');

        return [
            'used' => new DataSeries($metric, 'used', __('graph.vminfo.used_memory'), 'B', style: new SeriesStyle(SeriesRole::Used, fill: true)),
            'total' => new DataSeries($metric, 'total', __('graph.vminfo.total_memory'), 'B', style: new SeriesStyle(SeriesRole::Total)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, base: 1024, legendTitle: __('graph.vminfo.memory'));
    }
}
