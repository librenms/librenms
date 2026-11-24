<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindCacheDeletedGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'cache']);

        return [
            'crddtme' => new DataSeries($metric, 'crddtme', __('graph.application.bind.mem_exhaustion')),
            'crddtte' => new DataSeries($metric, 'crddtte', __('graph.application.bind.ttl_expiration')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'psychedelic');
    }
}
