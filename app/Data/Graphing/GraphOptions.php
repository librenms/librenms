<?php

/**
 * GraphOptions.php
 *
 * Presentation options chosen by the graph (scale and axis), as opposed to GraphParameters which come from the request
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

readonly class GraphOptions
{
    /**
     * @param  bool|null  $scaleRigid  defaults to rigid when neither scaleMin nor scaleMax is set
     * @param  string  $legendTitle  heading above the series labels
     * @param  string  $palette  the graph_colours palette for series without a color
     */
    public function __construct(
        public ?float $scaleMin = null,
        public ?float $scaleMax = null,
        public ?bool $scaleRigid = null,
        public ?int $base = null,
        public ?int $unitsExponent = null,
        public bool $logarithmic = false,
        public bool $altYGrid = false,
        public ?string $verticalLabel = null,
        public ?string $leftAxisFormat = null,
        public ?string $rightAxis = null,
        public ?string $rightAxisLabel = null,
        public ?int $unitsLength = null,
        public string $legendTitle = '',
        public string $palette = 'mixed',
    ) {
    }
}
