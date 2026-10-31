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
use Illuminate\Support\Arr;
use LibreNMS\Data\Store\Rrd;

class MultiSimplexSeparatedGraphBuilder extends RrdGraphBuilder
{
    private string $unitText = '';
    private ?float $divider = null;
    private ?float $multiplier = null;
    private bool $textOrig = false;
    private bool $totalVisible = false;
    private string $totalUnits = '';
    /** @var array<string, array{color: ?string}> */
    private array $seriesOptions = [];

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

    /**
     * @param  string|string[]  $series  series keys
     */
    public function setSeriesOptions(string|array $series, ?string $color = null): self
    {
        foreach (Arr::wrap($series) as $index) {
            $this->seriesOptions[$index] = [
                'color' => $color,
            ];
        }

        return $this;
    }

    /**
     * @return array{color: ?string}
     */
    private function getOptions(string $series): array
    {
        return $this->seriesOptions[$series] ?? [
            'color' => null,
        ];
    }

    public function build(GraphParameters $params): array
    {
        $precision = self::PRECISION;
        $previous = $params->visible('previous');
        $prev_from = $params->prev_from;
        $from = $params->from;
        $period = $params->period;

        $descr_len = $this->totalVisible ? 12 : 16;

        $rrd_options = [];
        $rrd_options[] = 'COMMENT:' . Rrd::fixedSafeDescr($this->unitText, $descr_len) . "        Now      Min     Max     Avg\l";

        $seperatorX = '';
        $thingX = '';
        $plusX = '';
        $plusesX = '';
        $stack = '';

        $i = 0;
        foreach ($this->data->getSeries() as $index => $series) {
            $filename = $this->filename($series);

            if ($filename === null) {
                continue;
            }

            $ds = $series->field;
            array_push($rrd_options, ...$this->defs($ds . $i, $filename, $series));

            $color = $this->nextColor($this->getOptions($index)['color']);
            $descr = Rrd::fixedSafeDescr($series->description, $descr_len);

            if ($previous) {
                $rrd_options[] = 'DEF:' . $i . 'X=' . $filename . ':' . $ds . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
                $rrd_options[] = 'SHIFT:' . $i . "X:$period";
                $thingX .= $seperatorX . $i . 'X,UN,0,' . $i . 'X,IF';
                $plusesX .= $plusX;
                $seperatorX = ',';
                $plusX = ',+';
            }

            if ($this->totalVisible) {
                $rrd_options[] = 'VDEF:tot' . $ds . $i . '=' . $ds . $i . ',TOTAL';
            }

            if ($i > 0) {
                $stack = ':STACK';
            }

            $g_defname = $ds;
            if ($this->multiplier !== null) {
                $g_defname = $ds . '_cdef';
                $rrd_options[] = 'CDEF:' . $g_defname . $i . '=' . $ds . $i . ',' . $this->multiplier . ',*';
                $rrd_options[] = 'CDEF:' . $g_defname . $i . 'min=' . $ds . $i . 'min,' . $this->multiplier . ',*';
                $rrd_options[] = 'CDEF:' . $g_defname . $i . 'max=' . $ds . $i . 'max,' . $this->multiplier . ',*';
            } elseif ($this->divider !== null) {
                $g_defname = $ds . '_cdef';
                $rrd_options[] = 'CDEF:' . $g_defname . $i . '=' . $ds . $i . ',' . $this->divider . ',/';
                $rrd_options[] = 'CDEF:' . $g_defname . $i . 'min=' . $ds . $i . 'min,' . $this->divider . ',/';
                $rrd_options[] = 'CDEF:' . $g_defname . $i . 'max=' . $ds . $i . 'max,' . $this->divider . ',/';
            }

            $rrd_options[] = 'AREA:' . $g_defname . $i . '#' . $color . ':' . $descr . "$stack";

            $t_defname = $this->textOrig ? $ds : $g_defname;
            $rrd_options[] = 'GPRINT:' . $t_defname . $i . ':LAST:%5.' . $precision . 'lf%s';
            $rrd_options[] = 'GPRINT:' . $t_defname . $i . 'min:MIN:%5.' . $precision . 'lf%s';
            $rrd_options[] = 'GPRINT:' . $t_defname . $i . 'max:MAX:%5.' . $precision . 'lf%s';
            $rrd_options[] = 'GPRINT:' . $t_defname . $i . ':AVERAGE:%5.' . $precision . 'lf%s\\n';

            if ($this->totalVisible) {
                $rrd_options[] = 'GPRINT:tot' . $ds . $i . ':%6.' . $precision . 'lf%s' . Rrd::safeDescr($this->totalUnits);
            }

            $rrd_options[] = 'COMMENT:\\n';

            $i++;
        }

        if ($previous) {
            if ($this->multiplier !== null) {
                $rrd_options[] = 'CDEF:X=' . $thingX . $plusesX . ',' . $this->multiplier . ',*';
            } elseif ($this->divider !== null) {
                $rrd_options[] = 'CDEF:X=' . $thingX . $plusesX . ',' . $this->divider . ',/';
            } else {
                $rrd_options[] = 'CDEF:X=' . $thingX . $plusesX;
            }

            $rrd_options[] = 'AREA:X#99999999:';
            $rrd_options[] = 'LINE1.25:X#666666:';
        }

        return $rrd_options;
    }
}
