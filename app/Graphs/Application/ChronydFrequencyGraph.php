<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ChronydFrequencyGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'frequency' => new DataSeries($metric, 'frequency', __('graph.application.chronyd.error_rate')),
            'residual_frequency' => new DataSeries($metric, 'residual_frequency', __('graph.application.chronyd.ref_clk_offset')),
            'skew' => new DataSeries($metric, 'skew', __('graph.application.chronyd.sys_clk_skew')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.chronyd.seconds_ppm'), palette: 'mixed');
    }
}
