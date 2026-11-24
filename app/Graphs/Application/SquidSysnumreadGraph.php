<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidSysnumreadGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'sysnumreads' => new DataSeries($metric, 'sysnumreads', __('graph.application.squid.http_io_reads'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'mixed');
    }
}
