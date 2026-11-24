<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class FbsdNfsServerCacheGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'inprog' => new DataSeries($metric, 'inprog', __('graph.application.fbsd_nfs_server.inprog'), style: $style),
            'idem' => new DataSeries($metric, 'idem', __('graph.application.fbsd_nfs_server.idem'), style: $style),
            'nonidem' => new DataSeries($metric, 'nonidem', __('graph.application.fbsd_nfs_server.nonidem'), style: $style),
            'misses' => new DataSeries($metric, 'misses', __('graph.application.fbsd_nfs_server.misses'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'mixed');
    }
}
