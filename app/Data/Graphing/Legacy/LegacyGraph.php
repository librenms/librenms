<?php

/**
 * LegacyGraph.php
 *
 * Adapter for the graph include files in includes/html/graphs
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

namespace App\Data\Graphing\Legacy;

use App\Data\Graphing\AbstractRrdGraph;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphPageContext;
use App\Data\Graphing\GraphParameters;
use App\Facades\Rrd;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\InvalidGraph;

class LegacyGraph extends AbstractRrdGraph
{
    private readonly LegacyGraphScript $script;
    private ?LegacyAuthResult $authResult = null;
    private ?LegacyGraphResult $graphResult = null;

    /**
     * @throws InvalidGraph
     */
    public function __construct(GraphParameters $params)
    {
        parent::__construct($params);

        $this->script = LegacyGraphScript::find($params->type, $params->subtype) ?? throw new InvalidGraph;
    }

    public function authorize(): bool
    {
        return $this->authResult()->authorized;
    }

    public function getPageContext(): GraphPageContext
    {
        return $this->authResult()->pageContext;
    }

    /**
     * Graph files may set the title, unauthorized users do not get to run them
     */
    public function getTitle(): string
    {
        $title = $this->authorize() ? $this->graphResult()->title : '';

        return $title ?: parent::getTitle();
    }

    protected function options(): GraphOptions
    {
        return $this->graphResult()->options;
    }

    protected function rrdDefinition(): array
    {
        return $this->graphResult()->rrdOptions;
    }

    /**
     * Only checked after a failed draw, each check may be a request to rrdcached
     */
    protected function dataFilesMissing(): bool
    {
        $files = $this->graphResult()->rrdFiles;

        foreach ($files as $file) {
            if (Rrd::checkRrdExists($file)) {
                return false;
            }
        }

        return $files !== [];
    }

    /**
     * @throws GraphException
     */
    private function authResult(): LegacyAuthResult
    {
        return $this->authResult ??= $this->script->runAuth($this->requestedDevice(), $this->requestedPort(), $this->params->input->all());
    }

    /**
     * @throws GraphException
     */
    private function graphResult(): LegacyGraphResult
    {
        return $this->graphResult ??= $this->script->runGraph($this->authResult(), $this->params);
    }
}
