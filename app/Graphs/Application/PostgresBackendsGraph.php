<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PostgresBackendsGraph extends PostgresGraph
{
    public function getSeries(): array
    {
        $metric = $this->databaseMetric();

        return [
            'backends' => new DataSeries($metric, 'backends', __('graph.application.postgres.backends')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(palette: 'mixed');
    }
}
