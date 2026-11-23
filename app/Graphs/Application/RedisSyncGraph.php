<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class RedisSyncGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'sync']);

        return [
            'full' => new DataSeries($metric, 'full', __('graph.application.redis.full')),
            'ok' => new DataSeries($metric, 'ok', __('graph.application.redis.ok')),
            'err' => new DataSeries($metric, 'err', __('graph.series.error')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.redis.sync'), palette: 'mixed');
    }
}
