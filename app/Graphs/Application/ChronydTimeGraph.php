<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ChronydTimeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'system_time' => new DataSeries($metric, 'system_time', __('graph.application.chronyd.clock_lag')),
            'last_offset' => new DataSeries($metric, 'last_offset', __('graph.application.chronyd.last_offset')),
            'rms_offset' => new DataSeries($metric, 'rms_offset', __('graph.application.chronyd.avg_offset')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.seconds'), palette: 'mixed');
    }
}
