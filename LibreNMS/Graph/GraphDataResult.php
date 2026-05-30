<?php

/**
 * GraphDataResult.php
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

class GraphDataResult
{
    /** @var GraphSeries[] */
    private array  $series  = [];
    private array  $markers = [];
    private string $source     = 'rrd';
    private bool   $fallback   = false;
    private ?string $emptyReason = null;
    private array   $warnings    = [];
    private array   $display     = [];
    private array   $variables   = [];

    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $title,
        public readonly string $subtitle,
        public readonly string $unit,
        public readonly int    $from,
        public readonly int    $to,
        public readonly int    $step,
        public readonly string $timezone = 'UTC',
    ) {}

    public function addSeries(GraphSeries $series): void  { $this->series[] = $series; }
    public function addMarker(array $marker): void        { $this->markers[] = $marker; }
    public function setSource(string $source): void       { $this->source = $source; }
    public function setFallback(bool $fb): void           { $this->fallback = $fb; }
    public function setEmptyReason(?string $reason): void { $this->emptyReason = $reason; }
    public function addWarning(string $warning): void     { $this->warnings[] = $warning; }
    public function setDisplay(array $display): void      { $this->display = $display; }
    public function setVariables(array $variables): void  { $this->variables = $variables; }
    public function overrideYAxisMin(int $min): void      { $this->display['yAxisMin'] = $min; }
    public function overrideYAxisMax(int $max): void      { $this->display['yAxisMax'] = $max; }

    /**
     * @return GraphSeries[]
     */
    public function series(): array
    {
        return $this->series;
    }

    public function toArray(): array
    {
        // Build y_axes from display config. If display['y_axes'] is provided (multi-axis graphs),
        // use it directly. Otherwise, synthesise a single axis from the scalar yAxisMin/yAxisMax hints.
        if (! empty($this->display['y_axes'])) {
            $yAxes = $this->display['y_axes'];
        } else {
            $yAxes = [[
                'unit'  => $this->unit,
                'scale' => $this->display['yAxisScale'] ?? 'linear',
                'min'   => $this->display['yAxisMin'] ?? null,
                'max'   => $this->display['yAxisMax'] ?? null,
            ]];
        }

        return [
            'status' => 'ok',
            'graph'  => [
                'id'       => $this->id,
                'type'     => $this->type,
                'title'    => $this->title,
                'subtitle' => $this->subtitle,
                'unit'     => $this->unit,
                'from'     => $this->from,
                'to'       => $this->to,
                'step'     => $this->step,
                'timezone' => $this->timezone,
                'display'  => $this->display,
                'variables' => $this->variables,
                'x_axis'     => ['type' => 'time'],
                'y_axes'     => $yAxes,
                'series'     => array_map(fn ($s) => $s->toArray(), $this->series),
                'markers'    => $this->markers,
                'meta'       => [
                    'source'        => $this->source,
                    'fallback_used' => $this->fallback,
                    'empty_reason'  => $this->emptyReason,
                    'warnings'      => $this->warnings,
                    'generated_at'  => time(),
                ],
            ],
        ];
    }
}
