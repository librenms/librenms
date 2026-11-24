<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisNetGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'net']);

        return [
            'input_bytes' => new DataSeries($metric, 'input_bytes', __('graph.series.input')),
            'output_bytes' => new DataSeries($metric, 'output_bytes', __('graph.series.output')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.redis.net'), palette: 'mixed');
    }
}
