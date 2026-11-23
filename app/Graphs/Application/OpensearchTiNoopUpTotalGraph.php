<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTiNoopUpTotalGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'ti']);

        return [
            'ti_noop_up_total' => new DataSeries($metric, 'ti_noop_up_total', __('graph.application.opensearch.noop_updates')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.noops_per_sec'), palette: 'greens');
    }
}
