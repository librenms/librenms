<?php

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\Formula;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;

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
                style: new SeriesStyle(SeriesRole::Value, range: true)),
            'loss' => new DataSeries($metric, new Formula('xmt', 'rcv', '-', 'xmt', '/', 100, '*'), __('graph.device.icmp_perf.loss'), '%',
                style: new SeriesStyle(SeriesRole::Loss, fill: true)),
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
