<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTmDocsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'tm']);

        return [
            'tm_docs' => new DataSeries($metric, 'tm_docs', __('graph.application.opensearch.merged_docs')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.ops_per_second'), palette: 'greens');
    }
}
