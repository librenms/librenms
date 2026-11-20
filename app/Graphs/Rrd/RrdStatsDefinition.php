<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesSum;
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

    /** @var array<string, array{label: string, step: int, minRange: int}> label is a translation key, shown when the graph covers at least minRange seconds, a shorter range only shows NaN */
    private const array PERIODS = [
        'hourly' => ['label' => 'graphs.stats.hour', 'step' => 3600, 'minRange' => 0],
        'daily' => ['label' => 'graphs.stats.day', 'step' => 86400, 'minRange' => 129600],
        'weekly' => ['label' => 'graphs.stats.week', 'step' => 604800, 'minRange' => 691200],
    ];

    /** @var list<string> */
    public private(set) array $definitions = [];
    /** @var list<string> */
    public private(set) array $drawing = [];

    /**
     * @param  GraphTheme  $theme  gives each line the next color of the graph palette
     * @param  bool  $overlaid  inverted series are drawn above the axis, over the others
     * @param  bool  $nameSeries  start each label with the series description, so the lines of several series can be told apart
     */
    public function __construct(
        private readonly StatsOptions $options,
        private readonly GraphParameters $params,
        private readonly GraphTheme $theme,
        private readonly bool $overlaid,
        private readonly bool $nameSeries,
    ) {
    }

    /**
     * The labels of the lines and legend rows addSeries() adds for a series, before padding.
     * RrdSeriesDefinition pads every label of the graph to the longest one, so it needs them before anything is drawn.
     * The periods shown at this range are keyed like PERIODS, the percentiles by their index in StatsOptions::percentiles,
     * such as ['periods' => ['hourly' => ['avg' => '1 hour avg', 'min' => '1 hour min', 'max' => '1 hour max']], 'percentiles' => [0 => '25th Percentile']].
     * With several series each label starts with the series description, such as 'Jitter 1 hour avg'.
     *
     * @return array{periods: array<string, array{avg: string, min: string, max: string}>, percentiles: array<int, string>}
     */
    public function labels(DataSeries|SeriesSum $series): array
    {
        $labels = ['periods' => [], 'percentiles' => []];
        if ($this->params->height <= self::MIN_HEIGHT) {
            return $labels;
        }

        $prefix = $this->nameSeries ? $series->description . ' ' : '';
        $range = $this->params->to - $this->params->from;
        foreach (self::PERIODS as $period => ['label' => $labelKey, 'minRange' => $minRange]) {
            if ($this->options->$period && $range >= $minRange) {
                $periodLabel = __($labelKey);
                foreach (['avg', 'min', 'max'] as $row) {
                    $labels['periods'][$period][$row] = $prefix . __("graphs.stats.$row", ['period' => $periodLabel]);
                }
            }
        }

        if (! LibrenmsConfig::get('graph_stat_percentile_disable')) {
            foreach ($this->options->percentiles as $index => $percentile) {
                $labels['percentiles'][$index] = $prefix . __('graphs.stats.percentile', ['percentile' => $percentile]);
            }
        }

        return $labels;
    }

    /**
     * @param  string  $id  the series values, defined by RrdSeriesDefinition as $id, {$id}min and {$id}max
     * @param  float  $drawScale  the draw only scale of the series, applied to the lines but not the legend values
     * @param  Closure(string $period, string $suffix, string $cf, string $range): list<string>  $periodValueDefs  defines the value of the series
     *                                                                                                             for $id$period$suffix, such as ds0hourlymax
     * @param  int  $labelWidth  characters each label is padded to, the longest label of the graph
     */
    public function addSeries(string $id, DataSeries|SeriesSum $series, float $drawScale, Closure $periodValueDefs, int $labelWidth): void
    {
        if ($this->params->height <= self::MIN_HEIGHT) {
            return;
        }

        $factor = $drawScale * ($series->style->invert && ! $this->overlaid ? -1 : 1);
        $factorRpn = RrdSeriesDefinition::number($factor);
        $unit = str_replace(['%', ':'], ['%%', '\\:'], $series->unit);
        $labels = $this->labels($series);
        // colors are taken even for lines that are not drawn, so each line keeps its color at any range
        $percentileColors = array_map(fn () => $this->theme->next(null), $this->options->percentiles);

        foreach (self::PERIODS as $period => ['step' => $step]) {
            $color = $this->theme->next(null);
            $rows = $labels['periods'][$period] ?? null;
            if ($rows === null) {
                continue;
            }

            $periodId = $id . $period;
            foreach (['' => 'AVERAGE', 'min' => 'MIN', 'max' => 'MAX'] as $suffix => $cf) {
                // defined like the series values, so scale, offset, formulas, sums and min and max fields apply the same
                array_push($this->definitions, ...$periodValueDefs($period, $suffix, $cf, ":step=$step"));
            }

            $drawnId = $periodId;
            if ($factor != 1) {
                $drawnId = $periodId . 'drawn';
                $this->definitions[] = "CDEF:$drawnId=$periodId,$factorRpn,*";
            }

            // only the average is drawn, min and max are legend rows
            $this->drawing[] = "LINE1.25:$drawnId#$color:" . Rrd::fixedSafeDescr($rows['avg'], $labelWidth);
            array_push($this->drawing, ...$this->legendRow($periodId, $unit));
            foreach (['min', 'max'] as $suffix) {
                // two spaces take the place of the color box, aligning the label with the others
                $this->drawing[] = 'COMMENT:  ' . Rrd::fixedSafeDescr($rows[$suffix], $labelWidth);
                array_push($this->drawing, ...$this->legendRow($periodId . $suffix, $unit));
            }
        }

        // empty when graph_stat_percentile_disable is set
        foreach ($labels['percentiles'] as $index => $label) {
            $percentile = $this->options->percentiles[$index];
            // indexed, a percentile such as 99.9 is not a valid name
            $percentileId = "{$id}p$index";
            $this->definitions[] = "VDEF:$percentileId=$id,$percentile,PERCENTNAN";

            // a VDEF can not be multiplied, so scale a line holding the value instead
            $lineId = $percentileId;
            if ($factor != 1) {
                $lineId = $percentileId . 'drawn';
                array_push($this->definitions, "CDEF:{$lineId}line=$id,POP,$percentileId,$factorRpn,*", "VDEF:$lineId={$lineId}line,FIRST");
            }

            $this->drawing[] = "HRULE:$lineId#$percentileColors[$index]:" . Rrd::fixedSafeDescr($label, $labelWidth);
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
