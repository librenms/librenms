<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisKeyspaceGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'keyspace']);

        return [
            'hits' => new DataSeries($metric, 'hits', __('graph.units.hits')),
            'misses' => new DataSeries($metric, 'misses', __('graph.units.misses')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.redis.keyspace'), palette: 'mixed');
    }
}
