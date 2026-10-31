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

use App\Data\Graphing\AbstractRrdGraph;
use App\Data\Graphing\Builders\MultiLineGraphBuilder;
use App\Data\Graphing\Builders\MultiSimplexSeparatedGraphBuilder;
use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Interfaces\Data\Graphing\GraphDataInterface;

class ProcessorGraph extends AbstractRrdGraph implements GraphDataInterface
{
    public function authorize(): bool
    {
        $device = $this->getDevice();

        return $device !== null && $this->allows('view', $device);
    }

    public function getTitle(): string
    {
        return $this->getDevice()->display ?? '';
    }

    public function getSeries(): array
    {
        $series = [];
        foreach ($this->getDevice()->processors ?? [] as $proc) {
            $series["proc_$proc->processor_id"] = new DataSeries($proc->metricIdentity(), 'usage', $proc->getFormattedDescription());
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, scaleMax: 100);
    }

    protected function rrdDefinition(): array
    {
        $series = $this->getSeries();

        if (empty($series)) {
            throw new GraphException('No Processors', 'No Data');
        }

        if (LibrenmsConfig::getOsSetting($this->getDevice()?->os, 'processor_stacked')) {
            return MultiSimplexSeparatedGraphBuilder::data($this, $this->getValidator())
                ->unitText('Load %')
                ->colors('oranges')
                ->divider(count($series))
                ->textOrig()
                ->build($this->params);
        }

        return MultiLineGraphBuilder::data($this, $this->getValidator())
            ->units('', 'Load %')
            ->setSeriesOptions(array_keys($series), area: true)
            ->build($this->params);
    }
}
