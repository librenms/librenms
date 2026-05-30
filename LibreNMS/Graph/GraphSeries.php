<?php

/**
 * GraphSeries.php
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

class GraphSeries
{
    public array $data  = [];
    public float $min   = INF;
    public float $max   = -INF;
    public float $sum   = 0.0;
    public int   $count = 0;
    private array $extraStats = [];

    public function __construct(
        public readonly string  $name,
        public readonly string  $key,
        public readonly string  $unit,
        public readonly string  $type        = 'line',
        public readonly bool    $area        = false,
        public readonly ?string $stack       = null,
        public readonly string  $color       = '663399',
        public readonly ?string $lineColor   = null,
        public readonly float   $areaOpacity = 1.0,
        public readonly float   $lineOpacity = 1.0,
        public readonly float   $lineWidth   = 1.25,
        public readonly bool    $negate      = false,
        public readonly int     $yAxisIndex  = 0,
    ) {}

    public function addPoint(int $timestampMs, float $value): void
    {
        $this->data[]  = [$timestampMs, $value];
        $this->min     = min($this->min, $value);
        $this->max     = max($this->max, $value);
        $this->sum    += $value;
        $this->count++;
    }

    public function stats(): array
    {
        $last = empty($this->data) ? null : end($this->data)[1];

        return [
            'min'  => $this->count > 0 ? $this->min : null,
            'max'  => $this->count > 0 ? $this->max : null,
            'avg'  => $this->count > 0 ? round($this->sum / $this->count, 4) : null,
            'last' => $last,
        ] + $this->extraStats;
    }

    public function addStat(string $name, mixed $value): void
    {
        $this->extraStats[$name] = $value;
    }

    public function toArray(): array
    {
        return [
            'name'  => $this->name,
            'key'   => $this->key,
            'type'  => $this->type,
            'unit'  => $this->unit,
            'data'  => $this->data,
            'yAxisIndex' => $this->yAxisIndex,
            'style' => ['area' => $this->area, 'stack' => $this->stack, 'color' => $this->color, 'lineColor' => $this->lineColor, 'areaOpacity' => $this->areaOpacity, 'lineOpacity' => $this->lineOpacity, 'lineWidth' => $this->lineWidth, 'negate' => $this->negate],
            'stats' => $this->stats(),
        ];
    }
}
