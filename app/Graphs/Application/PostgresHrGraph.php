<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PostgresHrGraph extends PostgresGraph
{
    public function getSeries(): array
    {
        $metric = $this->databaseMetric();

        return [
            'read' => new DataSeries($metric, 'read', __('graph.application.postgres.blocks_read'), style: new SeriesStyle(fill: true)),
            'hit' => new DataSeries($metric, 'hit', __('graph.application.postgres.buffer_hits'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'mixed');
    }
}
