<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTsegCountGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tseg']);

        return [
            'tseg_count' => new DataSeries($metric, 'tseg_count', __('graph.application.opensearch.segments_count')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.opensearch.segments'), palette: 'greens');
    }
}
