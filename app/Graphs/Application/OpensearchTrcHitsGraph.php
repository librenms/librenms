<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTrcHitsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'trc']);

        return [
            'trc_hits' => new DataSeries($metric, 'trc_hits', __('graph.application.opensearch.rc_hits')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.ops_per_second'), palette: 'greens');
    }
}
