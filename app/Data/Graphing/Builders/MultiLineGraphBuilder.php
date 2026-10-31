<?php

/**
 * MultiLineGraphBuilder.php
 *
 * Lines (optionally with area or inverted) with now, min, max and average legend values
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
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Arr;
use LibreNMS\Data\Store\Rrd;

class MultiLineGraphBuilder extends RrdGraphBuilder
{
    private string $unitDescription = '';
    private string $units = '';
    /** @var array<string, array{color: ?string, area: bool, areaColor: ?string, invert: bool}> */
    private array $seriesOptions = [];

    public function units(string $units, ?string $description = null): self
    {
        $this->units = $units;
        $this->unitDescription = $description ?? $this->units;

        return $this;
    }

    /**
     * @param  string|string[]  $series  series keys
     */
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

    public function build(GraphParameters $params): array
    {
        $precision = self::PRECISION;
        $descr_len = 14;
        $rrd_options = [];
        $rrd_options[] = 'COMMENT:' . Rrd::fixedSafeDescr($this->unitDescription, $descr_len) . "      Now      Min      Max     Avg\l";

        $stackedVal = LibrenmsConfig::get('webui.graph_stacked') ? '1' : '-1';
        $rrd_optionsb = [];

        $i = 0;
        foreach ($this->data->getSeries() as $index => $series) {
            $filename = $this->filename($series);

            if ($filename === null) {
                continue;
            }

            $id = 'ds' . $i;
            array_push($rrd_options, ...$this->defs($id, $filename, $series));
            $i++;

            $options = $this->getOptions($index);
            $color = $this->nextColor($options['color']);
            $descr = Rrd::fixedSafeDescr($series->description, $descr_len);

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

            $rrd_optionsb[] = 'GPRINT:' . $id . ':LAST:%5.' . $precision . 'lf%s' . $this->units;
            $rrd_optionsb[] = 'GPRINT:' . $id . 'min:MIN:%5.' . $precision . 'lf%s' . $this->units;
            $rrd_optionsb[] = 'GPRINT:' . $id . 'max:MAX:%5.' . $precision . 'lf%s' . $this->units;
            $rrd_optionsb[] = 'GPRINT:' . $id . ':AVERAGE:%5.' . $precision . "lf%s{$this->units}\\n";
        }

        array_push($rrd_options, ...$rrd_optionsb);
        $rrd_options[] = 'HRULE:0#555555';

        return $rrd_options;
    }
}
