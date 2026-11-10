<?php

/**
 * GraphInterface.php
 *
 * A graph that can be authorized and rendered to an image, independent of the backend that draws it
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

namespace LibreNMS\Interfaces\Data\Graphing;

use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphPageContext;
use LibreNMS\Exceptions\GraphException;

interface GraphInterface
{
    /**
     * Can the current user view this graph
     *
     * @throws GraphException
     */
    public function authorize(): bool;

    /**
     * What the graph page shows around this graph, such as the graphed device and port
     *
     * @throws GraphException
     */
    public function getPageContext(): GraphPageContext;

    /**
     * @throws GraphException
     */
    public function getTitle(): string;

    /**
     * A shell command that draws this graph, for troubleshooting
     *
     * @throws GraphException
     */
    public function getDebugCommand(): ?string;

    /**
     * Validates input and authorization, then draws the image
     *
     * @throws GraphException
     */
    public function render(): GraphImage;
}
