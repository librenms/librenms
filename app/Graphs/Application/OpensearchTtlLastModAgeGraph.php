<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTtlLastModAgeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'ttl']);

        return [
            'ttl_last_mod_age' => new DataSeries($metric, 'ttl_last_mod_age', __('graph.application.opensearch.last_mod_age')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.seconds'), palette: 'greens');
    }
}
