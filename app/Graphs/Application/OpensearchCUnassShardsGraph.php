<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchCUnassShardsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'c']);

        return [
            'c_unass_shards' => new DataSeries($metric, 'c_unass_shards', __('graph.application.opensearch.unassigned')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.shards'), palette: 'greens');
    }
}
