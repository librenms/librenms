<?php

/**
 * GraphSeriesDefinition.php
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
 * @copyright  2026 Tristan Rhodes
 * @author     Tristan Rhodes <tristan.rhodes@gmail.com>
 */

namespace LibreNMS\Graph;

class GraphSeriesDefinition
{
    /** @var array<string, MetricBinding> */
    private array $bindings = [];
    /** @var MetricBinding[] */
    private array $bindingList = [];

    /**
     * @param MetricBinding[] $bindings
     */
    public function __construct(
        public readonly string $name,
        public readonly string $key,
        public readonly string $unit,
        public readonly string $type = 'line',
        public readonly bool $area = false,
        public readonly ?string $stack = null,
        public readonly string $color = '663399',
        public readonly ?string $lineColor = null,
        public readonly float $areaOpacity = 1.0,
        public readonly float $lineOpacity = 1.0,
        public readonly float $lineWidth = 1.25,
        public readonly bool $negate = false,
        public readonly int $yAxisIndex = 0,
        array $bindings = [],
    ) {
        foreach ($bindings as $binding) {
            $this->bindingList[] = $binding;
            $this->bindings[$binding->source()] ??= $binding;
        }
    }

    public function binding(string $source): ?MetricBinding
    {
        return $this->bindings[$source] ?? null;
    }

    /**
     * @return MetricBinding[]
     */
    public function bindings(?string $source = null): array
    {
        if ($source === null) {
            return $this->bindingList;
        }

        return array_values(array_filter($this->bindingList, fn (MetricBinding $binding) => $binding->source() === $source));
    }
}
