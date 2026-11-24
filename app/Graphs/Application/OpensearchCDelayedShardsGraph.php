<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchCDelayedShardsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'c']);

        return [
            'c_delayed_shards' => new DataSeries($metric, 'c_delayed_shards', __('graph.application.opensearch.delayed_shards')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.shards'), palette: 'greens');
    }
}
