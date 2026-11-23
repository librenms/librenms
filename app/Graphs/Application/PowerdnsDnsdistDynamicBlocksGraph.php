<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PowerdnsDnsdistDynamicBlocksGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'dynamic_blocked' => new DataSeries($metric, 'dynamic_blocked', __('graph.application.powerdns_dnsdist.dropped'), style: new SeriesStyle(fill: true)),
            'dynamic_block_size' => new DataSeries($metric, 'dynamic_block_size', __('graph.application.powerdns_dnsdist.size'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.powerdns_dnsdist.dynamic_blocks'));
    }
}
