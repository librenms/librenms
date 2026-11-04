<?php

/**
 * AbstractRrdGraph.php
 *
 * Base for graphs drawn by rrdtool graph
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

namespace App\Data\Graphing;

use App\Data\Graphing\Rrd\RrdOptionsBuilder;
use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\RRD\Backend\RrdBackendInterface;

abstract class AbstractRrdGraph extends AbstractGraph
{
    /**
     * The graph elements: DEF, CDEF, LINE, AREA, GPRINT, etc.
     *
     * @return array<int, string>
     *
     * @throws GraphException
     */
    abstract protected function rrdDefinition(): array;

    protected function options(): GraphOptions
    {
        return new GraphOptions;
    }

    /**
     * The full rrdtool graph argument list
     *
     * @return array<int, string>
     *
     * @throws GraphException
     */
    public function getRrdCommandOptions(): array
    {
        $this->checkRenderable();

        return $this->buildRrdOptions();
    }

    public function getDebugCommand(): ?string
    {
        // rrd file names are relative to the rrd directory
        return 'cd ' . escapeshellarg((string) LibrenmsConfig::get('rrd_dir')) . ' && ' . implode(' ', array_map(escapeshellarg(...), [
            'rrdtool',
            'graph',
            '-',
            ...$this->getRrdCommandOptions(),
        ]));
    }

    protected function draw(): GraphImage
    {
        $rrd_options = $this->buildRrdOptions();

        try {
            $image = app(RrdBackendInterface::class)->graph($rrd_options, session('preferences.timezone'));

            return new GraphImage($this->params->imageFormat, $this->getTitle(), $image);
        } catch (RrdGraphException $e) {
            if ($this->dataFilesMissing()) {
                throw new NoGraphData('No Data files', $e);
            }

            throw new RrdGraphException('Error: ' . $e->getMessage(), 'Draw Error', $e);
        }
    }

    /**
     * Whether a failed draw is because of missing data files
     */
    protected function dataFilesMissing(): bool
    {
        return false;
    }

    /**
     * @return array<int, string>
     */
    private function buildRrdOptions(): array
    {
        $definition = $this->rrdDefinition();

        if (empty($definition)) {
            throw new GraphException('Graph Definition Error', 'Def Error');
        }

        return [
            ...(new RrdOptionsBuilder($this->params))->build($this->getTitle(), $this->options()),
            ...$definition,
        ];
    }
}
