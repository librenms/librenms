<?php

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class UptimeGraph extends DeviceGraph
{
    private const int SECONDS_PER_DAY = 86400;

    public function getTitle(): string
    {
        return $this->device()->display;
    }

    public function getSeries(): array
    {
        return [
            'uptime' => new DataSeries($this->metric('uptime'), 'uptime', __('graph.device.uptime.uptime'), __('graph.device.uptime.unit'), scale: 1 / self::SECONDS_PER_DAY, style: new SeriesStyle(fill: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        // days read badly with SI prefixes: half a day is not 500 millidays
        return new GraphOptions(scaleMin: 0, unitsExponent: 0, verticalLabel: __('graph.device.uptime.days'), palette: 'greens');
    }
}
