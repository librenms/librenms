<?php

/**
 * RrdSeriesRenderer.php
 *
 * Draws data series from rrd files with rrdtool graph
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

namespace App\Data\Graphing\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\TimeSeries\Rrd\RrdFileResolver;
use LibreNMS\Exceptions\GraphException;

class RrdSeriesRenderer
{
    private readonly RrdGraphRenderer $renderer;

    public function __construct(
        private readonly GraphParameters $params,
        private readonly RrdFileResolver $rrdFiles,
    ) {
        $this->renderer = new RrdGraphRenderer($params);
    }

    /**
     * @param  array<string, DataSeries>  $series
     *
     * @throws GraphException
     */
    public function render(string $title, array $series, GraphOptions $options): GraphImage
    {
        return $this->renderer->draw($title, $this->commandOptions($title, $series, $options));
    }

    /**
     * @param  array<string, DataSeries>  $series
     *
     * @throws GraphException
     */
    public function debugCommand(string $title, array $series, GraphOptions $options): string
    {
        return $this->renderer->debugCommand($this->commandOptions($title, $series, $options));
    }

    /**
     * @param  array<string, DataSeries>  $series
     * @return array<int, string>
     *
     * @throws GraphException
     */
    private function commandOptions(string $title, array $series, GraphOptions $options): array
    {
        $definition = new RrdSeriesDefinition($series, $options, $this->rrdFiles)->build($this->params);

        return $this->renderer->commandOptions($title, $options, $definition);
    }
}
