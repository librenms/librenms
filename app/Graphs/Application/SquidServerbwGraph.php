<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidServerbwGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'serverinkb' => new DataSeries($metric, 'serverinkb', __('graph.application.squid.server_in'), style: $style),
            'serveroutkb' => new DataSeries($metric, 'serveroutkb', __('graph.application.squid.server_out'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.kilobytes_per_second'), palette: 'mixed');
    }
}
