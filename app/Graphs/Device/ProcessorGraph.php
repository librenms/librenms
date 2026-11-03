<?php

/**
 * ProcessorGraph.php
 *
 * Processor usage for all processors of a device
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Graphs\Device;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;

class ProcessorGraph extends DeviceGraph
{
    public function getTitle(): string
    {
        return $this->getDevice()->display ?? '';
    }

    public function getSeries(): array
    {
        $processors = $this->getDevice()->processors ?? collect();

        // stacked processors are drawn as their share of the device total
        $style = $this->isStacked()
            ? new SeriesStyle(stacked: true, drawScale: 1 / max(1, $processors->count()))
            : new SeriesStyle(fill: true);

        $series = [];
        foreach ($processors as $processor) {
            $series["proc_$processor->processor_id"] = new DataSeries(MetricIdentity::forProcessor($processor), 'usage', $processor->getFormattedDescription(), style: $style);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, scaleMax: 100, legendTitle: 'Load %', palette: $this->isStacked() ? 'oranges' : 'mixed');
    }

    private function isStacked(): bool
    {
        return (bool) LibrenmsConfig::getOsSetting($this->getDevice()?->os, 'processor_stacked');
    }
}
