<?php

/**
 * RrdMetricBinding.php
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

class RrdMetricBinding implements MetricBinding
{
    public const SOURCE = 'rrd';

    /**
     * @param string|array $rrdName RRD name component(s), relative to the device's RRD directory
     * @param string|array $ds Data source name, or multiple data sources passed to transform keyed by name
     * @param callable|null $transform Applied to each raw value before storage, e.g. fn($v) => $v * 8
     */
    public function __construct(
        public readonly string|array $rrdName,
        public readonly string|array $ds,
        public readonly string $consolidation = 'AVERAGE',
        public readonly ?int $step = null,
        public readonly mixed $transform = null,
    ) {}

    public function source(): string
    {
        return self::SOURCE;
    }
}
