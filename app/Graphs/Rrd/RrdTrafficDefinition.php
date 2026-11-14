<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\TrafficOptions;
use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Util\Number;

/**
 * Adds traffic percentiles, transferred bytes, comparison lines and port speeds to a series definition.
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

    public function __construct(private readonly TrafficOptions $options, private readonly GraphParameters $params)
    {
    }

    public function addSeries(string $key, string $id, string $filename, DataSeries $series, float $drawScale): void
    {
        $factor = $drawScale * ($series->style->invert && ! LibrenmsConfig::get('webui.graph_stacked') ? -1 : 1);
        $this->summarize($id, $series, $factor);

        if ($this->params->to > time()) {
            array_push($this->defs,
                "VDEF:{$id}slope={$id}max,LSLSLOPE",
                "VDEF:{$id}intercept={$id}max,LSLINT",
                "CDEF:{$id}trend={$id}max,POP,{$id}slope,COUNT,*,{$id}intercept,+,$factor,*",
            );
            $this->drawing[] = "LINE2:{$id}trend#" . ($series->style->color ?? '666666') . ':' . Rrd::safeDescr($series->description . ' Linear Prediction') . '\\n:dashes=8';
        }

        if ($this->params->visible('previous')) {
            $previous = $id . 'prev';
            foreach (['' => 'AVERAGE', 'min' => 'MIN', 'max' => 'MAX'] as $suffix => $cf) {
                array_push($this->defs,
                    "DEF:{$previous}raw$suffix=$filename:$series->field:$cf:start={$this->params->prev_from}:end={$this->params->from}",
                    "SHIFT:{$previous}raw$suffix:{$this->params->period}",
                    "CDEF:$previous$suffix={$previous}raw$suffix,$series->scale,*",
                );
            }
            $this->defs[] = "CDEF:{$previous}drawn=$previous,$factor,*";
            $this->drawing[] = "LINE1.25:{$previous}drawn#" . ($key === 'in' ? '333300' : '000099') . ':' . Rrd::fixedSafeDescr('Prev ' . $series->description, 16);
            $unit = str_replace(['%', ':'], ['%%', '\\:'], $series->unit);
            array_push($this->drawing,
                "GPRINT:$previous:LAST:%5.2lf%s$unit",
                "GPRINT:{$previous}min:MIN:%5.2lf%s$unit",
                "GPRINT:{$previous}max:MAX:%5.2lf%s$unit",
                "GPRINT:$previous:AVERAGE:%5.2lf%s$unit",
                "GPRINT:{$previous}pct:%5.2lf%s$unit\\n",
            );
            $this->summarize($previous, $series, $factor, 'prev');
        }
    }

    private function summarize(string $id, DataSeries $series, float $factor, string $period = ''): void
    {
        $percentile = $this->options->percentile;
        $scale = $this->options->totalScale;
        array_push($this->defs,
            "VDEF:{$id}pct=$id,$percentile,PERCENT",
            "CDEF:{$id}pctdrawn=$id,POP,{$id}pct,$factor,*",
            "VDEF:{$id}pctline={$id}pctdrawn,FIRST",
            "CDEF:{$id}bytes=$id,$scale,*",
            "VDEF:{$id}total={$id}bytes,TOTAL",
        );
        $this->drawing[] = "HRULE:{$id}pctline#" . ($period === '' ? 'aa0000' : '00aaaa');
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
            if ($this->options->highestPercentile) {
                $defs[] = "CDEF:traffic{$period}high=" . implode(',', $values) . str_repeat(',MAX', count($values) - 1);
                $defs[] = "VDEF:traffic{$period}pct=traffic{$period}high,{$this->options->percentile},PERCENT";
            }
        }

        return $defs;
    }

    /** @return list<string> */
    public function drawing(): array
    {
        $drawing = $this->drawing;
        foreach ($this->totals as $period => $totals) {
            if ($this->options->highestPercentile) {
                $drawing[] = "HRULE:traffic{$period}pct#FF0000:" . ($period === '' ? 'Highest' : 'Prev Highest');
                $drawing[] = "GPRINT:traffic{$period}pct:%6.2lf%s\\n";
            }
            if ($this->params->visible('total')) {
                $drawing[] = "GPRINT:traffic{$period}bytes:" . ($period === '' ? 'Total' : 'Prev Total') . " %6.2lf%s{$this->options->totalUnit}";
                array_push($drawing, ...$totals);
                $drawing[array_key_last($drawing)] .= '\\l';
            }
        }

        $speeds = array_filter($this->options->speeds);
        if (count(array_unique($speeds)) === 1) {
            $speeds = ['' => reset($speeds)];
        }
        foreach ($speeds as $key => $speed) {
            $value = $key === 'out' && ! LibrenmsConfig::get('webui.graph_stacked') && ! $this->options->speedZoom ? -$speed : $speed;
            $type = $this->options->speedZoom ? 'LINE2' : 'HRULE';
            $label = ($key === '' ? '' : ucfirst($key) . ' ') . 'Port Speed ' . Number::formatSi($speed, 2, 0, 'bps');
            $drawing[] = "$type:$value#000000:" . Rrd::safeDescr($label) . '\\n';
        }

        return $drawing;
    }
}
