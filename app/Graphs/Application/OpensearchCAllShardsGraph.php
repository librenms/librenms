<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchCAllShardsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'c']);

        return [
            'c_act_pri_shards' => new DataSeries($metric, 'c_act_pri_shards', __('graph.application.opensearch.active_primary')),
            'c_act_shards' => new DataSeries($metric, 'c_act_shards', __('graph.application.opensearch.active')),
            'c_rel_shards' => new DataSeries($metric, 'c_rel_shards', __('graph.application.opensearch.relocating')),
            'c_init_shards' => new DataSeries($metric, 'c_init_shards', __('graph.application.opensearch.initializing')),
            'c_delayed_shards' => new DataSeries($metric, 'c_delayed_shards', __('graph.application.opensearch.delayed_unass')),
            'c_unass_shards' => new DataSeries($metric, 'c_unass_shards', __('graph.application.opensearch.unassigned')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.shards'), palette: 'psychedelic');
    }
}
