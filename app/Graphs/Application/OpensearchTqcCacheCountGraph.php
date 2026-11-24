<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTqcCacheCountGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tqc']);

        return [
            'tqc_cache_count' => new DataSeries($metric, 'tqc_cache_count', __('graph.application.opensearch.qc_cache_count')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.cache_count'), palette: 'greens');
    }
}
