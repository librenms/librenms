<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchCTaskMaxInTimeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'c']);

        return [
            'c_task_max_in_time' => new DataSeries($metric, 'c_task_max_in_time', __('graph.application.opensearch.task_max_in_time')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.milliseconds'), palette: 'greens');
    }
}
