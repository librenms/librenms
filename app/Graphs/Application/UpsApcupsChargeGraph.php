<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class UpsApcupsChargeGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'charge' => new DataSeries($metric, 'charge', __('graph.application.ups_apcups.charge'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.percent'));
    }
}
