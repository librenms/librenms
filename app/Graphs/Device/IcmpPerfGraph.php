<?php

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\Formula;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\ThemeColor;

/**
 * Round trip time of pings, the range between the fastest and slowest of each interval, and the percent of pings lost
 */
class IcmpPerfGraph extends DeviceGraph
{
    public function getTitle(): string
    {
        return $this->device()->display;
    }

    public function getSeries(): array
    {
        $metric = $this->metric('ping');

        return [
            'rtt' => new DataSeries($metric, 'avg', __('graph.device.icmp_perf.rtt'), 'ms', minField: 'min', maxField: 'max',
                style: new SeriesStyle(color: new ThemeColor('272B30', 'F2F2F2'), rangeColor: new ThemeColor('D0D0D0', '3E444C'))),
            'loss' => new DataSeries($metric, new Formula('xmt', 'rcv', '-', 'xmt', '/', 100, '*'), __('graph.device.icmp_perf.loss'), '%',
                style: new SeriesStyle(color: 'D42E08', fill: true, fillColor: 'D42E08')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            scaleMin: 0,
            axisUnit: 'ms',
            unitsExponent: 0,
            verticalLabel: __('graph.device.icmp_perf.latency'),
            rightAxisLabel: __('graph.device.icmp_perf.loss_axis'),
        );
    }
}
