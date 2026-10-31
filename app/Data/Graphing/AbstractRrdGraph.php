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

use App\Data\TimeSeries\Rrd\RrdFileValidator;
use App\Facades\Rrd;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Interfaces\Data\Graphing\RrdGraphInterface;

abstract class AbstractRrdGraph extends AbstractGraph implements RrdGraphInterface
{
    private ?RrdFileValidator $validator = null;

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

    public function getRrdCommandOptions(): array
    {
        $this->checkRenderable();

        return $this->buildRrdOptions();
    }

    protected function draw(): GraphImage
    {
        $rrd_options = $this->buildRrdOptions();

        try {
            return new GraphImage($this->params->imageFormat, $this->getTitle(), Rrd::graph($rrd_options));
        } catch (RrdGraphException $e) {
            if ($this->getValidator()->foundNoFiles()) {
                throw new GraphException('No Data files', 'No Data', $e);
            }

            throw new RrdGraphException('Error: ' . $e->getMessage(), 'Draw Error', $e);
        }
    }

    /**
     * Tracks which rrd files exist, used to report missing data instead of a draw error
     */
    protected function getValidator(): RrdFileValidator
    {
        return $this->validator ??= app(RrdFileValidator::class);
    }

    /**
     * @return array<int, string|int|float>
     */
    private function buildRrdOptions(): array
    {
        // the definition is built first, legacy graphs set options and title while building it
        $definition = $this->rrdDefinition();

        if (empty($definition)) {
            throw new GraphException('Graph Definition Error', 'Def Error');
        }

        return [
            ...$this->params->toRrdOptions($this->getTitle()),
            ...$this->options()->toRrdOptions($this->params),
            ...$definition,
        ];
    }
}
