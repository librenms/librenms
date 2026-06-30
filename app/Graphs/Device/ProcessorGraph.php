<?php

/**
 * ProcessorGraph.php
 *
 * -Description-
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

use App\Data\Graphing\AbstractGraph;
use App\Data\Graphing\Builders\MultiLineGraphBuilder;
use App\Data\Graphing\Builders\MultiSimplexSeparatedGraphBuilder;
use App\Data\Graphing\DataSeries;
use App\Facades\LibrenmsConfig;
use App\Models\Processor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Interfaces\Data\Graphing\GraphDataInterface;

class ProcessorGraph extends AbstractGraph implements GraphDataInterface
{
    public string $type = 'device';
    public string $subtype = 'processor';

    /** @var Collection<Processor> */
    private Collection $processors;

    protected function init(): void
    {
        $this->processors = $this->device->exists ? $this->device->processors : new Collection;
    }

    public function authorize(): bool
    {
        if ($processor = $this->processors->first()) {
            return Gate::allows('view', $processor);
        }

        return $this->device->exists && Gate::allows('view', $this->device);
    }

    public function getGraphTitle(): string
    {
        return $this->device->display ?? '';
    }

    public function getSeries(): array
    {
        $series = [];
        foreach ($this->processors as $proc) {
            $metric = $proc->toMetricIdentity('processor');
            $series["proc_{$proc->id}"] = new DataSeries($metric, 'usage', $proc->getFormattedDescription());
        }

        return $series;
    }

    public function rrdDefinition(): array
    {
        $series = $this->getSeries();
        $series_count = count($series);

        if (LibrenmsConfig::getOsSetting($this->device->os, 'processor_stacked')) {
            return MultiSimplexSeparatedGraphBuilder::data($this)
                ->unitText('Load %')
                ->colors('oranges')
                ->scaleMin(0)
                ->scaleMax(100)
                ->divider((float) max(1, $series_count))
                ->textOrig()
                ->build($this->params);
        }

        return MultiLineGraphBuilder::data($this)
            ->units('', 'Load %')
            ->scaleMin(0)
            ->scaleMax(100)
            ->setSeriesOptions(array_keys($series), area: true)
            ->build($this->params);
    }
}
