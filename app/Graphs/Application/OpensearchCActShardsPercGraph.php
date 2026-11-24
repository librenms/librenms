<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchCActShardsPercGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'c']);

        return [
            'c_act_shards_perc' => new DataSeries($metric, 'c_act_shards_perc', __('graph.application.opensearch.act_shards_percent')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.shards_percent'), palette: 'greens');
    }
}
