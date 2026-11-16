<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\SeriesStyle;

class NetGraph extends VminfoGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric('vm.net');

        return [
            'in' => new DataSeries($metric, 'in', 'In', 'bps', 8, new SeriesStyle(color: '608720', fill: true, fillColor: '90B040', maxFillColor: 'D7FFC7')),
            'out' => new DataSeries($metric, 'out', 'Out', 'bps', 8, new SeriesStyle(color: '606090', fill: true, fillColor: '8080C0', maxFillColor: 'E0E0FF', invert: true)),
        ];
    }
}
