<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisDefragGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'defrag']);

        return [
            'hits' => new DataSeries($metric, 'hits', __('graph.units.hits')),
            'misses' => new DataSeries($metric, 'misses', __('graph.units.misses')),
            'key_hits' => new DataSeries($metric, 'key_hits', __('graph.application.redis.key_hits')),
            'key_misses' => new DataSeries($metric, 'key_misses', __('graph.application.redis.key_misses')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.redis.defrag'), palette: 'mixed');
    }
}
