<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class PostgresCrGraph extends PostgresGraph
{
    public function getSeries(): array
    {
        $metric = $this->databaseMetric();

        return [
            'rollbacks' => new DataSeries($metric, 'rollbacks', __('graph.application.postgres.rollbacks'), style: new SeriesStyle(fill: true)),
            'commits' => new DataSeries($metric, 'commits', __('graph.application.postgres.commits'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'mixed');
    }
}
