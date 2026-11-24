<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTtlUncomOpsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'ttl']);

        return [
            'ttl_uncom_ops' => new DataSeries($metric, 'ttl_uncom_ops', __('graph.application.opensearch.uncom_ops')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.ops'), palette: 'greens');
    }
}
