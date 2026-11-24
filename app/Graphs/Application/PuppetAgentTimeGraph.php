<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PuppetAgentTimeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'time']);

        return [
            'catalog_application' => new DataSeries($metric, 'catalog_application', __('graph.application.puppet_agent.catalog_application')),
            'config_retrieval' => new DataSeries($metric, 'config_retrieval', __('graph.application.puppet_agent.config_retrieval')),
            'convert_catalog' => new DataSeries($metric, 'convert_catalog', __('graph.application.puppet_agent.convert_catalog')),
            'fact_generation' => new DataSeries($metric, 'fact_generation', __('graph.application.puppet_agent.fact_generation')),
            'node_retrieval' => new DataSeries($metric, 'node_retrieval', __('graph.application.puppet_agent.node_retrieval')),
            'plugin_sync' => new DataSeries($metric, 'plugin_sync', __('graph.application.puppet_agent.plugin_sync')),
            'schedule' => new DataSeries($metric, 'schedule', __('graph.application.puppet_agent.schedule')),
            'transaction_evaluation' => new DataSeries($metric, 'transaction_evaluation', __('graph.application.puppet_agent.transaction_evaluation')),
            'total' => new DataSeries($metric, 'total', __('graph.series.total')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.puppet_agent.runtime_per_second'), palette: 'psychedelic');
    }
}
