<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SquidPagefaultsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'syspagefaults' => new DataSeries($metric, 'syspagefaults', __('graph.application.squid.page_faults'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'mixed');
    }
}
