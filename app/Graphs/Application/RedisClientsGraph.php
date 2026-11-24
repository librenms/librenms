<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisClientsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'clients']);

        return [
            'connected' => new DataSeries($metric, 'connected', __('graph.application.redis.connected')),
            'blocked' => new DataSeries($metric, 'blocked', __('graph.application.redis.blocked')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.clients'), palette: 'mixed');
    }
}
