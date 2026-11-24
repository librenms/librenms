<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisCommandsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'commands']);

        return [
            'processed' => new DataSeries($metric, 'processed', __('graph.application.redis.processed')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.commands'), palette: 'mixed');
    }
}
