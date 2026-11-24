<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class SdfsinfoBlocksGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'dup_data' => new DataSeries($metric, 'dup_data', __('graph.application.sdfsinfo.duplicate_data_written_gb'), style: new SeriesStyle(fill: true)),
            'blocks_unique' => new DataSeries($metric, 'blocks_unique', __('graph.application.sdfsinfo.unique_blocks_gb'), style: new SeriesStyle(fill: true)),
            'blocks_compressed' => new DataSeries($metric, 'blocks_compressed', __('graph.application.sdfsinfo.compressed_blocks_gb'), style: new SeriesStyle(fill: true)),
            'cluster_copies' => new DataSeries($metric, 'cluster_copies', __('graph.application.sdfsinfo.cluster_copies'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.blocks'));
    }
}
