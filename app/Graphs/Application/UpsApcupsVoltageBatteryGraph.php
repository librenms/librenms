<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class UpsApcupsVoltageBatteryGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'battery_nominal' => new DataSeries($metric, 'battery_nominal', __('graph.application.ups_apcups.nominal'), style: new SeriesStyle(fill: true)),
            'battery_voltage' => new DataSeries($metric, 'battery_voltage', __('graph.application.ups_apcups.current'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.volts'));
    }
}
