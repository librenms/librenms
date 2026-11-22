<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesSum;
use App\Data\Graphing\TrafficOptions;
use Closure;
use LibreNMS\Data\Store\Rrd;

/**
 * Adds transferred bytes, comparison and prediction lines to a series definition.
 */
class RrdTrafficDefinition
{
    /** @var list<string> */
    private array $defs = [];
    /** @var list<string> */
    private array $drawing = [];
    /** @var array<string, list<string>> */
    private array $values = [];
    /** @var array<string, list<string>> */
    private array $totals = [];

    /**
     * @param  Closure(string $id, string $unit): list<string>  $legendValues  the legend values of a row, with the percentile columns of the graph
     * @param  Closure(string $id, float $factor, string $color): list<string>  $percentileLines  the percentile lines of a value
     */
    public function __construct(
        private readonly TrafficOptions $options,
        private readonly GraphParameters $params,
        private readonly GraphTheme $theme,
        private readonly Closure $legendValues,
        private readonly Closure $percentileLines,
    ) {
    }

    /**
     * @param  string|null  $color  the color the series is drawn in, null when it is not drawn
     * @param  Closure(string $period, string $suffix, string $cf, string $range, int $shift): list<string>  $periodValueDefs  defines the value of
     *                                                                                                                         the series for $id$period$suffix
     */
    public function addSeries(string $id, DataSeries|SeriesSum $series, float $drawFactor, ?string $color, Closure $periodValueDefs): void
    {
        $factor = RrdSeriesDefinition::number($drawFactor);
        $this->summarize($id, $series);

        if ($this->params->to > time()) {
            array_push($this->defs,
                "VDEF:{$id}slope={$id}max,LSLSLOPE",
                "VDEF:{$id}intercept={$id}max,LSLINT",
                "CDEF:{$id}trend={$id}max,POP,{$id}slope,COUNT,*,{$id}intercept,+,$factor,*",
            );
            $this->drawing[] = "LINE2:{$id}trend#" . ($color ?? $this->theme->trend()) . ':' . Rrd::safeDescr(__('graph.legend.prediction', ['series' => $series->description])) . '\\n:dashes=8';
        }

        if ($this->params->visible('previous')) {
            $previous = $id . 'prev';
            $range = ":start={$this->params->prev_from}:end={$this->params->from}";
            foreach (['' => 'AVERAGE', 'min' => 'MIN', 'max' => 'MAX'] as $suffix => $cf) {
                array_push($this->defs, ...$periodValueDefs('prev', $suffix, $cf, $range, $this->params->period));
            }
            $previousColor = $this->theme->previous($series->style->invert);
            $this->defs[] = "CDEF:{$previous}drawn=$previous,$factor,*";
            $this->drawing[] = "LINE1.25:{$previous}drawn#$previousColor:" . RrdSeriesDefinition::legendLabel(__('graph.legend.previous', ['series' => $series->description]));
            array_push($this->drawing, ...($this->legendValues)($previous, $series->unit));
            if ($series->style->percentileLines) {
                array_push($this->drawing, ...($this->percentileLines)($previous, $drawFactor, $previousColor));
            }
            $this->summarize($previous, $series, 'prev');
        }
    }

    private function summarize(string $id, DataSeries|SeriesSum $series, string $period = ''): void
    {
        $scale = $this->options->totalScale;
        array_push($this->defs,
            "CDEF:{$id}bytes=$id,$scale,*",
            "VDEF:{$id}total={$id}bytes,TOTAL",
        );
        $this->values[$period][] = $id;
        $this->totals[$period][] = "GPRINT:{$id}total:" . Rrd::safeDescr($series->description) . " %6.2lf%s{$this->options->totalUnit}";
    }

    /** @return list<string> */
    public function definitions(): array
    {
        $defs = $this->defs;
        foreach ($this->values as $period => $values) {
            $defs[] = "CDEF:traffic{$period}total=" . implode(',', $values) . str_repeat(',+', count($values) - 1) . ",{$this->options->totalScale},*";
            $defs[] = "VDEF:traffic{$period}bytes=traffic{$period}total,TOTAL";
            if ($this->options->highestPercentile !== null) {
                $defs[] = "CDEF:traffic{$period}high=" . implode(',', $values) . str_repeat(',MAX', count($values) - 1);
                $defs[] = "VDEF:traffic{$period}pct=traffic{$period}high," . RrdSeriesDefinition::number($this->options->highestPercentile) . ',PERCENTNAN';
            }
        }

        return $defs;
    }

    /** @return list<string> */
    public function drawing(): array
    {
        $drawing = $this->drawing;
        foreach ($this->totals as $period => $totals) {
            if ($this->options->highestPercentile !== null) {
                $drawing[] = "HRULE:traffic{$period}pct#{$this->theme->highestPercentile()}:" . __($period === '' ? 'graph.legend.highest' : 'graph.legend.previous_highest');
                $drawing[] = "GPRINT:traffic{$period}pct:%6.2lf%s\\n";
            }
            if ($this->params->visible('total')) {
                $drawing[] = "GPRINT:traffic{$period}bytes:" . __($period === '' ? 'graph.legend.total' : 'graph.legend.previous_total') . " %6.2lf%s{$this->options->totalUnit}";
                array_push($drawing, ...$totals);
                $drawing[array_key_last($drawing)] .= '\\l';
            }
        }

        return $drawing;
    }
}
