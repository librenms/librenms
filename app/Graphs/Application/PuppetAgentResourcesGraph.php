<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PuppetAgentResourcesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'resources']);

        return [
            'changed' => new DataSeries($metric, 'changed', __('graph.application.puppet_agent.changed')),
            'corrective_change' => new DataSeries($metric, 'corrective_change', __('graph.application.puppet_agent.corrective_change')),
            'failed' => new DataSeries($metric, 'failed', __('graph.application.puppet_agent.failed')),
            'failed_to_restart' => new DataSeries($metric, 'failed_to_restart', __('graph.application.puppet_agent.failed_to_restart')),
            'out_of_sync' => new DataSeries($metric, 'out_of_sync', __('graph.application.puppet_agent.out_of_sync')),
            'restarted' => new DataSeries($metric, 'restarted', __('graph.application.puppet_agent.restarted')),
            'scheduled' => new DataSeries($metric, 'scheduled', __('graph.application.puppet_agent.scheduled')),
            'skipped' => new DataSeries($metric, 'skipped', __('graph.application.puppet_agent.skipped')),
            'total' => new DataSeries($metric, 'total', __('graph.series.total')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.puppet_agent.resources'), palette: 'psychedelic');
    }
}
