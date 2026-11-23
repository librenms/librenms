<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisUsageGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'usage']);

        return [
            'allocated' => new DataSeries($metric, 'allocated', __('graph.application.redis.allocated')),
            'dataset' => new DataSeries($metric, 'dataset', __('graph.application.redis.dataset')),
            'lua' => new DataSeries($metric, 'lua', __('graph.application.redis.lua')),
            'overhead' => new DataSeries($metric, 'overhead', __('graph.application.redis.overhead')),
            'peak' => new DataSeries($metric, 'peak', __('graph.application.redis.peak')),
            'rss' => new DataSeries($metric, 'rss', __('graph.application.redis.rss')),
            'scripts' => new DataSeries($metric, 'scripts', __('graph.application.redis.scripts')),
            'startup' => new DataSeries($metric, 'startup', __('graph.application.redis.startup')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.redis.usage'), palette: 'mixed');
    }
}
