<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTsegNormsSizeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tseg']);

        return [
            'tseg_norms_size' => new DataSeries($metric, 'tseg_norms_size', __('graph.application.opensearch.seg_norms_size')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'greens');
    }
}
