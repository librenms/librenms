<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class SneckRuntimeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'runtime' => new DataSeries($this->metric(['stat' => 'run_time']), 'data', __('graph.application.sneck.runtime')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.seconds'), palette: 'psychedelic');
    }
}
