<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class UpsApcupsRemainingGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'time_remaining' => new DataSeries($metric, 'time_remaining', __('graph.application.ups_apcups.remaining'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.minutes'));
    }
}
