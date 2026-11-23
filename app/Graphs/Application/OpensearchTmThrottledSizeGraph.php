<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTmThrottledSizeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tm']);

        return [
            'tm_throttled_size' => new DataSeries($metric, 'tm_throttled_size', __('graph.application.opensearch.m_throt_size')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes_per_second'), palette: 'greens');
    }
}
