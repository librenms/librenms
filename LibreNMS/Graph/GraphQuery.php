<?php

/**
 * GraphQuery.php
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

use LibreNMS\Config as LibrenmsConfig;

class GraphQuery
{
    private const MIN_WIDTH = 1;
    private const MAX_WIDTH = 5000;
    private const MIN_HEIGHT = 1;
    private const MAX_HEIGHT = 3000;
    private const DEFAULT_MIN_STEP = 300;
    private const MAX_RANGE = 63244800; // 2 * 366 days
    private const MAX_POINTS = 10000;

    public readonly int $step;

    /**
     * @param array{device_id: int, port_id?: int, port_name?: string, sensor_id?: int,
     *             sensor_class?: string, sensor_type?: string, sensor_index?: int|string,
     *             sensor_descr?: string, poller_type?: string, mempool_id?: int,
     *             processor_id?: int, storage_id?: int, bill_id?: int} $entities
     *        Entity keys required depend on graph type. All entity-specific keys are optional
     *        at the type level; individual graph definitions document which keys they require.
     * @param array<string, mixed> $options  Resolved graph variables (e.g. duration)
     */
    public function __construct(
        public readonly string $scope,
        public readonly string $graphType,
        public readonly int    $from,
        public readonly int    $to,
        public readonly int    $width,
        public readonly int    $height,
        public readonly array  $entities,
        public readonly array  $options = [],
        ?int $step = null,
    ) {
        if ($this->from >= $this->to) {
            throw new \InvalidArgumentException('Graph query "from" must be earlier than "to".');
        }
        if ($this->width < self::MIN_WIDTH || $this->width > self::MAX_WIDTH) {
            throw new \InvalidArgumentException('Graph query width is outside the supported range.');
        }
        if ($this->height < self::MIN_HEIGHT || $this->height > self::MAX_HEIGHT) {
            throw new \InvalidArgumentException('Graph query height is outside the supported range.');
        }

        $range = $this->to - $this->from;
        if ($range > self::MAX_RANGE) {
            throw new \InvalidArgumentException('Graph query time range is too large.');
        }

        $this->step = $step ?? max(self::configuredMinStep($this->graphType), (int) ceil($range / $this->width));
        if ($this->step < 1) {
            throw new \InvalidArgumentException('Graph query step must be positive.');
        }
        if ((int) ceil($range / $this->step) > self::MAX_POINTS) {
            throw new \InvalidArgumentException('Graph query requests too many data points.');
        }
    }

    public static function fromRequest(
        string $scope,
        string $graphType,
        array  $entities,
        int    $from  = 0,
        int    $to    = 0,
        int    $width = 1200,
        int    $height = 300,
        array  $options = [],
    ): self {
        $to   = $to   ?: time();
        $from = $from ?: $to - 86400;

        return new self($scope, $graphType, $from, $to, $width, $height, $entities, $options);
    }

    public function withTimeRange(int $from, int $to): self
    {
        return new self(
            $this->scope,
            $this->graphType,
            $from,
            $to,
            $this->width,
            $this->height,
            $this->entities,
            $this->options,
            $this->step,
        );
    }

    public function withStep(int $step): self
    {
        return new self(
            $this->scope,
            $this->graphType,
            $this->from,
            $this->to,
            $this->width,
            $this->height,
            $this->entities,
            $this->options,
            $step,
        );
    }

    public function withOptions(array $options): self
    {
        return new self(
            $this->scope,
            $this->graphType,
            $this->from,
            $this->to,
            $this->width,
            $this->height,
            $this->entities,
            $options,
            $this->step,
        );
    }

    private static function configuredMinStep(string $graphType): int
    {
        $setting = $graphType === 'device_icmp_perf' ? 'ping_rrd_step' : 'rrd.step';

        return max(1, (int) LibrenmsConfig::get($setting, self::DEFAULT_MIN_STEP));
    }
}
