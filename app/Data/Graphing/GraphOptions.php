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
     */
    public function __construct(
        public ?float $scaleMin = null,
        public ?float $scaleMax = null,
        public ?bool $scaleRigid = null,
        public bool $sloped = true,
        public ?int $base = null,
        public ?int $unitsExponent = null,
        public bool $logarithmic = false,
        public bool $altYGrid = false,
        public ?string $verticalLabel = null,
        public ?string $leftAxisFormat = null,
        public ?string $rightAxis = null,
        public ?string $rightAxisLabel = null,
        public ?int $unitsLength = null,
    ) {
    }

    /**
     * @param  GraphParameters  $params  the requested scale is used when the graph does not set one
     * @return array<int, string|int|float>
     */
    public function toRrdOptions(GraphParameters $params): array
    {
        $options = [];
        $min = $this->scaleMin ?? $params->scale_min;
        $max = $this->scaleMax ?? $params->scale_max;
        $rigid = $this->scaleRigid ?? $params->scale_rigid;

        if ($min === null && $max === null) {
            $options[] = '--alt-autoscale-max';
            $rigid ??= true;
        }
        if ($min !== null) {
            array_push($options, '-l', $min);
        }
        if ($max !== null) {
            array_push($options, '-u', $max);
        }
        if ($rigid) {
            $options[] = '--rigid';
        }

        if ($this->sloped) {
            $options[] = '--slope-mode';
        }
        if ($this->altYGrid) {
            $options[] = '--alt-y-grid';
        }
        if ($this->base !== null) {
            array_push($options, '--base', $this->base);
        }
        if ($this->unitsExponent !== null) {
            array_push($options, '--units-exponent', $this->unitsExponent);
        }
        if ($this->logarithmic) {
            $options[] = '--logarithmic';
        }

        if ($this->rightAxis !== null) {
            array_push($options, '--right-axis', GraphParameters::escapeRrdParameter($this->rightAxis));
        }
        if ($this->rightAxisLabel !== null) {
            array_push($options, '--right-axis-label', GraphParameters::escapeRrdParameter($this->rightAxisLabel));
        }
        if ($this->leftAxisFormat !== null) {
            array_push($options, '--left-axis-format', GraphParameters::escapeRrdParameter($this->leftAxisFormat));
        }
        if ($this->unitsLength !== null) {
            array_push($options, '--units-length', $this->unitsLength);
        }
        if ($this->verticalLabel !== null) {
            array_push($options, '--vertical-label', GraphParameters::escapeRrdParameter($this->verticalLabel));
        }

        return $options;
    }
}
