<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisMemoryGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'memory']);

        return [
            'active' => new DataSeries($metric, 'active', __('graph.application.redis.active')),
            'allocated' => new DataSeries($metric, 'allocated', __('graph.application.redis.allocated')),
            'resident' => new DataSeries($metric, 'resident', __('graph.application.redis.resident')),
            'frag_bytes' => new DataSeries($metric, 'frag_bytes', __('graph.application.redis.frag_bytes')),
            'rss_bytes' => new DataSeries($metric, 'rss_bytes', __('graph.application.redis.rss_bytes')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.redis.memory'), palette: 'mixed');
    }
}
