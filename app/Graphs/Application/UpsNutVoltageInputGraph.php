<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class UpsNutVoltageInputGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'input_voltage' => new DataSeries($metric, 'input_voltage', __('graph.application.ups_nut.input'), style: new SeriesStyle(fill: true)),
            'line_nominal' => new DataSeries($metric, 'line_nominal', __('graph.application.ups_nut.nominal'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.volts'));
    }
}
