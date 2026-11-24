<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTsegSizeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tseg']);

        return [
            'tseg_size' => new DataSeries($metric, 'tseg_size', __('graph.application.opensearch.segment_size')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'greens');
    }
}
