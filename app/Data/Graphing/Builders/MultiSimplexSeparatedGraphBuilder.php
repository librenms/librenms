<?php

/**
 * MultiSimplexSeparatedGraphBuilder.php
 *
 * Stacked areas with now, min, max and average legend values and optional totals and previous period
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
use LibreNMS\Data\Store\Rrd;

class MultiSimplexSeparatedGraphBuilder extends RrdGraphBuilder
{
    private string $unitText = '';
    private ?float $divider = null;
    private ?float $multiplier = null;
    private bool $textOrig = false;
    private bool $totalVisible = false;
    private string $totalUnits = '';

    public function unitText(string $unitText): self
    {
        $this->unitText = $unitText;

        return $this;
    }

    public function divider(float $divider): self
    {
        $this->divider = $divider;

        return $this;
    }

    public function multiplier(float $multiplier): self
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    /**
     * Show the unscaled values in the legend when using a divider or multiplier
     */
    public function textOrig(bool $textOrig = true): self
    {
        $this->textOrig = $textOrig;

        return $this;
    }

    public function showTotal(bool $show = true, string $units = ''): self
    {
        $this->totalVisible = $show;
        $this->totalUnits = $units;

        return $this;
    }

    public function build(GraphParameters $params): array
    {
        $showPrevious = $params->visible('previous');
        $descr_len = $this->totalVisible ? 12 : 16;
        $scale = $this->scaleRpn();

        $rrd_options = ['COMMENT:' . Rrd::fixedSafeDescr($this->unitText, $descr_len) . "        Now      Min     Max     Avg\l"];
        $previousIds = [];

        $i = 0;
        foreach ($this->series as $key => $series) {
            $filename = $this->filename($series);

            if ($filename === null) {
                continue;
            }

            $id = $series->field . $i;
            array_push($rrd_options, ...$this->defs($id, $filename, $series));

            if ($showPrevious) {
                $previousId = $i . 'X';
                $rrd_options[] = "DEF:$previousId=$filename:$series->field:AVERAGE:start=$params->prev_from:end=$params->from";
                $rrd_options[] = "SHIFT:$previousId:$params->period";
                $previousIds[] = $previousId;
            }

            if ($this->totalVisible) {
                $rrd_options[] = "VDEF:tot$id=$id,TOTAL";
            }

            $drawnId = $id;
            if ($scale !== null) {
                $drawnId = $series->field . '_cdef' . $i;
                $rrd_options[] = "CDEF:$drawnId=$id$scale";
                $rrd_options[] = "CDEF:{$drawnId}min={$id}min$scale";
                $rrd_options[] = "CDEF:{$drawnId}max={$id}max$scale";
            }

            $color = $this->nextColor($this->style($key)->color);
            $descr = Rrd::fixedSafeDescr($series->description, $descr_len);
            $rrd_options[] = "AREA:$drawnId#$color:$descr" . ($i > 0 ? ':STACK' : '');

            array_push($rrd_options, ...$this->legendValues($this->textOrig ? $id : $drawnId));

            if ($this->totalVisible) {
                $rrd_options[] = "GPRINT:tot$id:%6." . self::PRECISION . 'lf%s' . Rrd::safeDescr($this->totalUnits);
            }

            $rrd_options[] = 'COMMENT:\\n';

            $i++;
        }

        if ($showPrevious) {
            array_push($rrd_options, 'CDEF:X=' . $this->sumRpn($previousIds) . $scale, 'AREA:X#99999999:', 'LINE1.25:X#666666:');
        }

        return $rrd_options;
    }

    /**
     * RPN suffix applying the multiplier or divider, null when values are not scaled
     */
    private function scaleRpn(): ?string
    {
        if ($this->multiplier !== null) {
            return ",$this->multiplier,*";
        }

        return $this->divider === null ? null : ",$this->divider,/";
    }

    /**
     * RPN adding the values of the given ids, treating unknown as zero
     *
     * @param  array<int, string>  $ids
     */
    private function sumRpn(array $ids): string
    {
        $values = array_map(fn (string $id) => "$id,UN,0,$id,IF", $ids);

        return implode(',', $values) . str_repeat(',+', max(0, count($ids) - 1));
    }
}
