<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTtlUncomSizeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'ttl']);

        return [
            'ttl_uncom_size' => new DataSeries($metric, 'ttl_uncom_size', __('graph.application.opensearch.uncom_size')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'greens');
    }
}
