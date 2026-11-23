<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisFragmentationGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'fragmentation']);

        return [
            'bytes' => new DataSeries($metric, 'bytes', __('graph.units.bytes')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.redis.fragmentation'), palette: 'mixed');
    }
}
