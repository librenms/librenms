<?php

/**
 * MultiLineGraphBuilder.php
 *
 * -Description-
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

namespace App\Data\Graphing\Builders;

use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\Traits\ColorIterator;
use App\Data\TimeSeries\Contracts\MetricValidator;
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Arr;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Interfaces\Data\Graphing\GraphDataInterface;

class MultiLineGraphBuilder
{
    use ColorIterator;

    private string $unitDescription = '';
    private string $units = '';
    private ?float $scaleMin = null;
    private ?float $scaleMax = null;
    private array $seriesOptions = [];

    public function __construct(
        private readonly GraphDataInterface $data,
        private readonly MetricValidator $validator,
    ) {
        $this->colors('mixed');
    }

    public static function data(GraphDataInterface $data): self
    {
        return resolve(self::class, [$data]);
    }

    public function units(string $units, ?string $description = null): self
    {
        $this->units = $units;
        $this->unitDescription = $description ?? $this->units;

        return $this;
    }

    public function scaleMin(float $scaleMin): self
    {
        $this->scaleMin = $scaleMin;

        return $this;
    }

    public function scaleMax(float $scaleMax): self
    {
        $this->scaleMax = $scaleMax;

        return $this;
    }

    public function setSeriesOptions(string|array $series, ?string $color = null, bool $area = false, ?string $areaColor = null, bool $invert = false): self
    {
        foreach (Arr::wrap($series) as $index) {
            $this->seriesOptions[$index] = [
                'color' => $color,
                'area' => $area,
                'areaColor' => $areaColor,
                'invert' => $invert,
            ];
        }

        return $this;
    }

    /**
     * @return array{color: ?string, area: bool, areaColor: ?string, invert: bool}
     */
    private function getOptions(string $series): array
    {
        return $this->seriesOptions[$series] ?? [
            'color' => null,
            'area' => false,
            'areaColor' => null,
            'invert' => false,
        ];
    }

    public function build(GraphParameters $graph_params): array
    {
        $float_precision = $graph_params->float_precision;

        if ($this->scaleMin !== null) {
            $graph_params->scale_min = (int) $this->scaleMin;
        }
        if ($this->scaleMax !== null) {
            $graph_params->scale_max = (int) $this->scaleMax;
        }

        $descr_len = 12;
        $rrd_options = [];
        $rrd_options[] = 'COMMENT:' . Rrd::fixedSafeDescr($this->unitDescription, $descr_len) . "      Now      Min      Max     Avg\l";

        $stackedVal = LibrenmsConfig::get('webui.graph_stacked') ? '1' : '-1';
        $rrd_optionsb = [];

        $i = 0;
        foreach ($this->data->getSeries() as $index => $series) {
            $options = $this->getOptions($index);

            $color = $this->nextColor($options['color']);

            $ds = $series->field;
            $filename = $this->validator->validate($series->metric);

            if ($filename === null) {
                continue;
            }

            $descr = Rrd::fixedSafeDescr($series->description, $descr_len);
            $id = 'ds' . $i++;

            $rrd_options[] = 'DEF:' . $id . "=$filename:$ds:AVERAGE";
            $rrd_options[] = 'DEF:' . $id . "min=$filename:$ds:MIN";
            $rrd_options[] = 'DEF:' . $id . "max=$filename:$ds:MAX";

            if ($options['invert']) {
                $rrd_options[] = 'CDEF:' . $id . 'i=' . $id . ',' . $stackedVal . ',*';
                $rrd_optionsb[] = 'LINE1.25:' . $id . 'i#' . $color . ":$descr";
                if ($options['area']) {
                    $rrd_optionsb[] = 'AREA:' . $id . 'i#' . ($options['areaColor'] ?? $color . '20');
                }
            } else {
                $rrd_optionsb[] = 'LINE1.25:' . $id . '#' . $color . ":$descr";
                if ($options['area']) {
                    $rrd_optionsb[] = 'AREA:' . $id . '#' . ($options['areaColor'] ?? $color . '20');
                }
            }

            $rrd_optionsb[] = 'GPRINT:' . $id . ':LAST:%5.' . $float_precision . 'lf%s' . $this->units;
            $rrd_optionsb[] = 'GPRINT:' . $id . 'min:MIN:%5.' . $float_precision . 'lf%s' . $this->units;
            $rrd_optionsb[] = 'GPRINT:' . $id . 'max:MAX:%5.' . $float_precision . 'lf%s' . $this->units;
            $rrd_optionsb[] = 'GPRINT:' . $id . ':AVERAGE:%5.' . $float_precision . "lf%s{$this->units}\\n";
        }

        array_push($rrd_options, ...$rrd_optionsb);
        $rrd_options[] = 'HRULE:0#555555';

        return $rrd_options;
    }
}
