<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ChronydRootGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'root_delay' => new DataSeries($metric, 'root_delay', __('graph.application.chronyd.root_clk_delay')),
            'root_dispersion' => new DataSeries($metric, 'root_dispersion', __('graph.application.chronyd.root_clk_disp')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.seconds'), palette: 'mixed');
    }
}
