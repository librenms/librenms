<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;

class DiskioBitsGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric('vm.diskio');

        return [
            'read' => new DataSeries($metric, 'read_bytes', __('graph.series.read'), 'bps', 8, new SeriesStyle(SeriesRole::In, fill: true)),
            'write' => new DataSeries($metric, 'write_bytes', __('graph.series.write'), 'bps', 8, new SeriesStyle(SeriesRole::Out, fill: true, invert: true)),
        ];
    }
}
