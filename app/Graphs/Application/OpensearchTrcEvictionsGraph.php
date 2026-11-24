<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTrcEvictionsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'trc']);

        return [
            'trc_evictions' => new DataSeries($metric, 'trc_evictions', __('graph.application.opensearch.rc_evictions')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.ops_per_second'), palette: 'greens');
    }
}
