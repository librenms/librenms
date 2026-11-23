<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PostgresSequentialGraph extends PostgresGraph
{
    public function getSeries(): array
    {
        $metric = $this->databaseMetric();

        return [
            'seqscan' => new DataSeries($metric, 'seqscan', __('graph.application.postgres.scans'), style: new SeriesStyle(fill: true)),
            'seqtupread' => new DataSeries($metric, 'seqtupread', __('graph.application.postgres.tuples_read'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.rows_per_second'), palette: 'mixed');
    }
}
