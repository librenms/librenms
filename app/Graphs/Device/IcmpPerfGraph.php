<?php

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\Formula;
use App\Data\Graphing\GraphOptions;
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
        $metric = $this->metric('icmp-perf');
        $dark = $this->params->isDark();

        return [
            'rtt' => new DataSeries($metric, 'avg', __('graph.device.icmp_perf.rtt'), 'ms', minField: 'min', maxField: 'max',
                style: new SeriesStyle(color: $dark ? 'D1D9EB' : '36393D', rangeColor: $dark ? '393D45CC' : 'CCD2DECC')),
            'loss' => new DataSeries($metric, new Formula('xmt', 'rcv', '-', 'xmt', '/', 100, '*'), __('graph.device.icmp_perf.loss'), '%',
                style: new SeriesStyle(color: 'D42E08', fill: true, fillColor: 'D42E08')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            scaleMin: 0,
            unitsExponent: 0,
            verticalLabel: __('graph.device.icmp_perf.latency'),
            leftAxisFormat: '%4.0lfms',
            rightAxis: '1:0',
            rightAxisLabel: __('graph.device.icmp_perf.loss_axis'),
        );
    }
}
