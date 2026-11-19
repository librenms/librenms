<?php

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;
use App\Facades\LibrenmsConfig;

/**
 * Processor usage for all processors of a device
 */
class ProcessorGraph extends DeviceGraph
{
    public function getTitle(): string
    {
        return $this->device()->display;
    }

    public function getSeries(): array
    {
        $device = $this->device();
        $processors = $device->processors;

        // stacked processors are drawn as their share of the device total
        $style = $this->isStacked()
            ? new SeriesStyle(stacked: true, stackAverage: true)
            : new SeriesStyle(fill: true);

        $series = [];
        foreach ($processors as $processor) {
            $processor->setRelation('device', $device);
            $metric = $processor->metricIdentity('processor');
            $series["proc_$processor->processor_id"] = new DataSeries($metric, 'usage', $processor->getFormattedDescription(), style: $style);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, scaleMax: 100, legendTitle: 'Load %', palette: $this->isStacked() ? 'oranges' : 'mixed');
    }

    private function isStacked(): bool
    {
        return (bool) LibrenmsConfig::getOsSetting($this->device()->os, 'processor_stacked');
    }
}
