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
use LibreNMS\Data\Store\Rrd;

class MultiLineGraphBuilder extends RrdGraphBuilder
{
    private string $unitDescription = '';
    private string $units = '';

    public function units(string $units, ?string $description = null): self
    {
        $this->units = $units;
        $this->unitDescription = $description ?? $this->units;

        return $this;
    }

    public function build(GraphParameters $params): array
    {
        $descr_len = 14;
        $invertMultiplier = LibrenmsConfig::get('webui.graph_stacked') ? '1' : '-1';

        $defs = [$this->legendHeader($this->unitDescription, $descr_len, $this->units)];
        $lines = [];

        $i = 0;
        foreach ($this->series as $key => $series) {
            $filename = $this->filename($series);

            if ($filename === null) {
                continue;
            }

            $id = 'ds' . $i;
            array_push($defs, ...$this->defs($id, $filename, $series));
            $i++;

            $style = $this->style($key);
            $color = $this->nextColor($style->color);
            $drawn = $id;

            if ($style->invert) {
                $drawn = $id . 'i';
                $defs[] = "CDEF:$drawn=$id,$invertMultiplier,*";
            }

            $lines[] = "LINE1.25:$drawn#$color:" . Rrd::fixedSafeDescr($series->description, $descr_len);
            if ($style->area) {
                $lines[] = "AREA:$drawn#" . ($style->areaColor ?? $color . '20');
            }

            array_push($lines, ...$this->legendValues($id, $this->units));
        }

        return [...$defs, ...$lines, 'HRULE:0#555555'];
    }
}
