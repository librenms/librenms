<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PuppetAgentLastRunGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'last_run']);

        return [
            'last_run' => new DataSeries($metric, 'last_run', __('graph.application.puppet_agent.last_run')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.minutes'), palette: 'psychedelic');
    }
}
