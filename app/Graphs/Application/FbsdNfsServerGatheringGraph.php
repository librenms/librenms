<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class FbsdNfsServerGatheringGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'writeops' => new DataSeries($metric, 'writeops', __('graph.application.fbsd_nfs_server.write_ops'), style: $style),
            'writerpc' => new DataSeries($metric, 'writerpc', __('graph.application.fbsd_nfs_server.write_rpc'), style: $style),
            'opsaved' => new DataSeries($metric, 'opsaved', __('graph.application.fbsd_nfs_server.ops_saved'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'mixed');
    }
}
