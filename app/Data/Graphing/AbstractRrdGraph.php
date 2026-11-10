<?php

/**
 * AbstractRrdGraph.php
 *
 * Base for graphs drawn by rrdtool graph from their own graph elements
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

use App\Data\Graphing\Rrd\RrdGraphRenderer;
use LibreNMS\Exceptions\GraphException;

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
        return $this->renderer()->debugCommand($this->getRrdCommandOptions());
    }

    protected function draw(): GraphImage
    {
        return $this->renderer()->draw($this->getTitle(), $this->buildRrdOptions(), $this->dataFilesMissing(...));
    }

    /**
     * Whether a failed draw is because of missing data files
     */
    protected function dataFilesMissing(): bool
    {
        return false;
    }

    private function renderer(): RrdGraphRenderer
    {
        return new RrdGraphRenderer($this->params);
    }

    /**
     * @return array<int, string>
     *
     * @throws GraphException
     */
    private function buildRrdOptions(): array
    {
        $definition = $this->rrdDefinition();

        return $this->renderer()->commandOptions($this->getTitle(), $this->options(), $definition);
    }
}
