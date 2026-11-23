<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;

class NetGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric('vm.net');

        return [
            'in' => new DataSeries($metric, 'in', __('graph.series.in'), 'bps', 8, new SeriesStyle(SeriesRole::In, fill: true, maxFill: true)),
            'out' => new DataSeries($metric, 'out', __('graph.series.out'), 'bps', 8, new SeriesStyle(SeriesRole::Out, fill: true, maxFill: true, invert: true)),
        ];
    }
}
