<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PostgresRowsGraph extends PostgresGraph
{
    public function getSeries(): array
    {
        $metric = $this->databaseMetric();

        return [
            'ret' => new DataSeries($metric, 'ret', __('graph.application.postgres.returned'), style: new SeriesStyle(fill: true)),
            'fetch' => new DataSeries($metric, 'fetch', __('graph.application.postgres.fetched'), style: new SeriesStyle(fill: true)),
            'ins' => new DataSeries($metric, 'ins', __('graph.application.postgres.inserted'), style: new SeriesStyle(fill: true)),
            'upd' => new DataSeries($metric, 'upd', __('graph.application.postgres.updated'), style: new SeriesStyle(fill: true)),
            'del' => new DataSeries($metric, 'del', __('graph.application.postgres.deleted'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.rows_per_second'), palette: 'mixed');
    }
}
