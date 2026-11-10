<?php

/**
 * RrdGraphRenderer.php
 *
 * Draws rrdtool graph definitions: adds the common options, runs rrdtool graph and builds the debug command
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

use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use Closure;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\RRD\Backend\RrdBackendInterface;

class RrdGraphRenderer
{
    public function __construct(private readonly GraphParameters $params)
    {
    }

    /**
     * The full rrdtool graph argument list
     *
     * @param  array<int, string>  $definition  the graph elements: DEF, CDEF, LINE, AREA, GPRINT, etc.
     * @return array<int, string>
     *
     * @throws GraphException
     */
    public function commandOptions(string $title, GraphOptions $options, array $definition): array
    {
        if (empty($definition)) {
            throw new GraphException('Graph Definition Error', 'Def Error');
        }

        return [
            ...new RrdOptionsBuilder($this->params)->build($title, $options),
            ...$definition,
        ];
    }

    /**
     * A shell command that draws the graph, rrd file names are relative to the rrd directory
     *
     * @param  array<int, string>  $commandOptions
     */
    public function debugCommand(array $commandOptions): string
    {
        return 'cd ' . escapeshellarg((string) LibrenmsConfig::get('rrd_dir')) . ' && ' . implode(' ', array_map(escapeshellarg(...), [
            'rrdtool',
            'graph',
            '-',
            ...$commandOptions,
        ]));
    }

    /**
     * Draw the graph in the timezone of the session
     *
     * @param  array<int, string>  $commandOptions
     * @param  (Closure(): bool)|null  $dataFilesMissing  whether a failed draw is because of missing data files
     *
     * @throws GraphException
     */
    public function draw(string $title, array $commandOptions, ?Closure $dataFilesMissing = null): GraphImage
    {
        try {
            $image = app(RrdBackendInterface::class)->graph($commandOptions, session('preferences.timezone'));

            return new GraphImage($this->params->imageFormat, $title, $image);
        } catch (RrdGraphException $e) {
            if ($dataFilesMissing !== null && $dataFilesMissing()) {
                throw new NoGraphData('No Data files', $e);
            }

            throw new RrdGraphException('Error: ' . $e->getMessage(), 'Draw Error', $e);
        }
    }
}
