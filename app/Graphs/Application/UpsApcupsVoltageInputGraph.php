<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class UpsApcupsVoltageInputGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'input_voltage' => new DataSeries($metric, 'input_voltage', __('graph.application.ups_apcups.input'), style: new SeriesStyle(fill: true)),
            'nominal_voltage' => new DataSeries($metric, 'nominal_voltage', __('graph.application.ups_apcups.nominal'), style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.units.volts'));
    }
}
