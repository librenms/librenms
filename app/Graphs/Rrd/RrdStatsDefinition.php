<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\StatsOptions;
use App\Facades\LibrenmsConfig;
use Closure;
use LibreNMS\Data\Store\Rrd;

/**
 * Adds rolling hourly, daily and weekly lines and percentile lines to a series definition.
 */
class RrdStatsDefinition
{
    /** graphs this short or shorter have no room for the extra lines */
    private const int MIN_HEIGHT = 25;

    /** @var array<string, array{label: string, step: int, minRange: int}> shown when the graph covers at least minRange seconds, a shorter range only shows NaN */
    private const array PERIODS = [
        'hourly' => ['label' => '1 hour', 'step' => 3600, 'minRange' => 0],
        'daily' => ['label' => '1 day', 'step' => 86400, 'minRange' => 129600],
        'weekly' => ['label' => '1 week', 'step' => 604800, 'minRange' => 691200],
    ];

    /** @var list<string> */
    public private(set) array $definitions = [];
    /** @var list<string> */
    public private(set) array $drawing = [];

    /**
     * @param  Closure(): string  $nextColor  the next color of the graph palette
     * @param  bool  $nameSeries  start each label with the series description, so the lines of several series can be told apart
     * @param  int  $labelWidth  characters each label is padded or cut to
     */
    public function __construct(
        private readonly StatsOptions $options,
        private readonly GraphParameters $params,
        private readonly Closure $nextColor,
        private readonly bool $nameSeries,
        private readonly int $labelWidth,
    ) {
    }

    /**
     * @param  string  $id  the series values, defined by RrdSeriesDefinition as $id, {$id}min and {$id}max
     * @param  float  $drawScale  the draw only scale of the series, applied to the lines but not the legend values
     */
    public function addSeries(string $id, string $filename, DataSeries $series, float $drawScale): void
    {
        if ($this->params->height <= self::MIN_HEIGHT) {
            return;
        }

        $factor = $drawScale * ($series->style->invert && ! LibrenmsConfig::get('webui.graph_stacked') ? -1 : 1);
        $unit = str_replace(['%', ':'], ['%%', '\\:'], $series->unit);
        $prefix = $this->nameSeries ? $series->description . ' ' : '';
        $range = $this->params->to - $this->params->from;
        // colors are taken even for lines that are not drawn, so each line keeps its color at any range
        $percentileColors = array_map(fn () => ($this->nextColor)(), $this->options->percentiles);

        foreach (self::PERIODS as $period => ['label' => $label, 'step' => $step, 'minRange' => $minRange]) {
            $color = ($this->nextColor)();
            if (! $this->options->$period || $range < $minRange) {
                continue;
            }

            $periodId = $id . $period;
            foreach (['' => 'AVERAGE', 'min' => 'MIN', 'max' => 'MAX'] as $suffix => $cf) {
                $source = "$filename:$series->field:$cf:step=$step";
                if ($series->scale == 1) {
                    $this->definitions[] = "DEF:$periodId$suffix=$source";
                } else {
                    array_push($this->definitions, "DEF:{$periodId}raw$suffix=$source", "CDEF:$periodId$suffix={$periodId}raw$suffix,$series->scale,*");
                }
            }

            $drawnId = $periodId;
            if ($factor != 1) {
                $drawnId = $periodId . 'drawn';
                $this->definitions[] = "CDEF:$drawnId=$periodId,$factor,*";
            }

            // only the average is drawn, min and max are legend rows
            $this->drawing[] = "LINE1.25:$drawnId#$color:" . Rrd::fixedSafeDescr("$prefix$label avg", $this->labelWidth);
            array_push($this->drawing, ...$this->legendRow($periodId, $unit));
            foreach (['min', 'max'] as $suffix) {
                // two spaces take the place of the color box, aligning the label with the others
                $this->drawing[] = 'COMMENT:  ' . Rrd::fixedSafeDescr("$prefix$label $suffix", $this->labelWidth);
                array_push($this->drawing, ...$this->legendRow($periodId . $suffix, $unit));
            }
        }

        if (LibrenmsConfig::get('graph_stat_percentile_disable')) {
            return;
        }

        foreach ($this->options->percentiles as $index => $percentile) {
            // indexed, a percentile such as 99.9 is not a valid name
            $percentileId = "{$id}p$index";
            $this->definitions[] = "VDEF:$percentileId=$id,$percentile,PERCENTNAN";

            // a VDEF can not be multiplied, so scale a line holding the value instead
            $lineId = $percentileId;
            if ($factor != 1) {
                $lineId = $percentileId . 'drawn';
                array_push($this->definitions, "CDEF:{$lineId}line=$id,POP,$percentileId,$factor,*", "VDEF:$lineId={$lineId}line,FIRST");
            }

            $this->drawing[] = "HRULE:$lineId#$percentileColors[$index]:" . Rrd::fixedSafeDescr("$prefix{$percentile}th Percentile", $this->labelWidth);
            $this->drawing[] = "GPRINT:$percentileId:%5." . RrdSeriesDefinition::PRECISION . "lf%s$unit\\n";
        }
    }

    /**
     * The Now, Min, Max and Avg legend columns of one value, ending the legend line.
     * Unlike a series row, every column summarizes the same value, so the Min column of the 1 hour max row is the lowest hourly maximum.
     *
     * @param  string  $vname  the value to summarize, such as ds0hourlymax
     * @param  string  $unit  the unit, already escaped for a format string
     * @return list<string> GPRINT elements, such as ['GPRINT:ds0hourly:LAST:%5.2lf%ss', ..., 'GPRINT:ds0hourly:AVERAGE:%5.2lf%ss\n']
     */
    private function legendRow(string $vname, string $unit): array
    {
        $row = [];
        foreach (['LAST', 'MIN', 'MAX', 'AVERAGE'] as $cf) {
            $row[] = "GPRINT:$vname:$cf:%5." . RrdSeriesDefinition::PRECISION . "lf%s$unit";
        }
        $row[3] .= '\\n';

        return $row;
    }
}
