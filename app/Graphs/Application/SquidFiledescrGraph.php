<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidFiledescrGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'curfiledescrcnt' => new DataSeries($metric, 'curfiledescrcnt', __('graph.application.squid.in_use'), style: $style),
            'curfiledescrmax' => new DataSeries($metric, 'curfiledescrmax', __('graph.series.max'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.squid.file_descr'), palette: 'mixed');
    }
}
