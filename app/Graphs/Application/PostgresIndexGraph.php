<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PostgresIndexGraph extends PostgresGraph
{
    public function getSeries(): array
    {
        $metric = $this->databaseMetric();

        return [
            'idxscan' => new DataSeries($metric, 'idxscan', __('graph.application.postgres.scans'), style: new SeriesStyle(fill: true)),
            'idxtupread' => new DataSeries($metric, 'idxtupread', __('graph.application.postgres.tuples_read'), style: new SeriesStyle(fill: true)),
            'idxtupfetch' => new DataSeries($metric, 'idxtupfetch', __('graph.application.postgres.tuples_fetched'), style: new SeriesStyle(fill: true)),
            'idxblksread' => new DataSeries($metric, 'idxblksread', __('graph.application.postgres.blocks_read'), style: new SeriesStyle(fill: true)),
            'idxblkshit' => new DataSeries($metric, 'idxblkshit', __('graph.application.postgres.buffer_hits'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.rows_per_second'), palette: 'mixed');
    }
}
