<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Exceptions\NoGraphData;

/**
 * Turns data series into the rrdtool graph elements that draw them and show their legend
 */
class RrdSeriesDefinition
{
    private const int LABEL_WIDTH = 16;
    private const int PRECISION = 2;

    /** @var array<string, array{suffix: string, cf: string}> legend columns and the value each one shows */
    private const array LEGEND_COLUMNS = [
        'Now' => ['suffix' => '', 'cf' => 'LAST'],
        'Min' => ['suffix' => 'min', 'cf' => 'MIN'],
        'Max' => ['suffix' => 'max', 'cf' => 'MAX'],
        'Avg' => ['suffix' => '', 'cf' => 'AVERAGE'],
    ];
    // a value is 5 digits and an SI prefix
    private const int LEGEND_VALUE_WIDTH = 6;
    // rrdtool separates legend items with 2 spaces
    private const int LEGEND_COLUMN_GAP = 2;
    // series labels start further right than the heading because of the color box
    private const int LEGEND_COLOR_BOX_WIDTH = 2;

    private int $colorIndex = 0;

    /**
     * @param  array<string, DataSeries>  $series
     */
    public function __construct(
        private readonly array $series,
        private readonly GraphOptions $options,
        private readonly RrdFileResolver $rrdFiles,
    ) {
    }

    /**
     * Series without an rrd file are left out
     *
     * @return array<int, string>
     *
     * @throws NoGraphData when none of the rrd files exist
     */
    public function build(GraphParameters $params): array
    {
        $defs = [];
        $drawing = [];
        $previousValues = [];
        $stacking = false;
        $inverted = false;

        $files = [];
        foreach ($this->series as $key => $series) {
            $filename = $this->rrdFiles->pathFor($series->metric);

            if ($filename !== null) {
                $files[$key] = $filename;
            }
        }

        if ($files === []) {
            throw new NoGraphData('No Data files');
        }

        $averaged = count(array_filter(array_keys($files), fn ($key) => $this->series[$key]->style->stackAverage));

        $i = 0;
        foreach ($files as $key => $filename) {
            $series = $this->series[$key];
            $id = 'ds' . $i++;
            $style = $series->style;
            $drawScale = $style->stackAverage ? $style->drawScale / $averaged : $style->drawScale;
            $color = $style->color ?? $this->nextColor();
            $label = Rrd::fixedSafeDescr($series->description, self::LABEL_WIDTH);
            $inverted = $inverted || $style->invert;

            array_push($defs, ...$this->valueDefs($id, $filename, $series));
            $drawnId = $this->drawnValue($id, $drawScale, $style->invert, $defs);

            if ($style->stacked) {
                $drawing[] = "AREA:$drawnId#$color:$label" . ($stacking ? ':STACK' : '');
                $stacking = true;

                if ($params->visible('previous')) {
                    array_push($defs, "DEF:{$id}prev=$filename:$series->field:AVERAGE:start=$params->prev_from:end=$params->from", "SHIFT:{$id}prev:$params->period");
                    $previousValues[] = "{$id}prev,UN,0,{$id}prev,IF" . $this->scaleRpn($series->scale * $drawScale);
                }
            } else {
                $drawing[] = "LINE1.25:$drawnId#$color:$label";
                if ($style->fill) {
                    $drawing[] = "AREA:$drawnId#" . ($style->fillColor ?? $color . '20');
                }
            }

            array_push($drawing, ...$this->legendValues($id, $series->unit));
        }

        if ($previousValues) {
            $defs[] = 'CDEF:previous=' . implode(',', $previousValues) . str_repeat(',+', count($previousValues) - 1);
            array_push($drawing, 'AREA:previous#99999999:', 'LINE1.25:previous#666666:');
        }

        if ($inverted) {
            $drawing[] = 'HRULE:0#555555';
        }

        return [$this->legendHeader(), ...$defs, ...$drawing];
    }

    /**
     * Define the average, min and max of a series as $id, {$id}min and {$id}max
     *
     * @return array<int, string>
     */
    private function valueDefs(string $id, string $filename, DataSeries $series): array
    {
        $defs = [];
        $scale = $this->scaleRpn($series->scale);

        foreach (['' => 'AVERAGE', 'min' => 'MIN', 'max' => 'MAX'] as $suffix => $cf) {
            if ($scale === '') {
                $defs[] = "DEF:$id$suffix=$filename:$series->field:$cf";
            } else {
                $defs[] = "DEF:{$id}raw$suffix=$filename:$series->field:$cf";
                $defs[] = "CDEF:$id$suffix={$id}raw$suffix$scale";
            }
        }

        return $defs;
    }

    /**
     * Apply the draw only transformations of the style
     *
     * @param  array<int, string>  $defs  receives the definition, if one is needed
     * @return string the id to draw
     */
    private function drawnValue(string $id, float $drawScale, bool $invert, array &$defs): string
    {
        $scale = $this->scaleRpn($drawScale * ($invert && ! LibrenmsConfig::get('webui.graph_stacked') ? -1 : 1));

        if ($scale === '') {
            return $id;
        }

        $defs[] = "CDEF:{$id}drawn=$id$scale";

        return $id . 'drawn';
    }

    /**
     * RPN suffix multiplying by the scale, empty when there is nothing to do
     */
    private function scaleRpn(float $scale): string
    {
        return $scale == 1 ? '' : ",$scale,*";
    }

    private function nextColor(): string
    {
        $palette = $this->options->palette;

        if (! LibrenmsConfig::has("graph_colours.$palette.$this->colorIndex")) {
            $this->colorIndex = 0;
        }

        return LibrenmsConfig::get("graph_colours.$palette." . $this->colorIndex++);
    }

    /**
     * The legend title and a label above each column of values, aligned with the values
     */
    private function legendHeader(): string
    {
        $unitWidth = max([0, ...array_map(fn (DataSeries $series) => strlen($series->unit), $this->series)]);
        $columnWidth = self::LEGEND_VALUE_WIDTH + $unitWidth + self::LEGEND_COLUMN_GAP;

        $header = Rrd::fixedSafeDescr($this->options->legendTitle, self::LABEL_WIDTH);
        $width = $columnWidth + self::LEGEND_COLOR_BOX_WIDTH;

        foreach (array_keys(self::LEGEND_COLUMNS) as $label) {
            $header .= str_pad($label, $width, ' ', STR_PAD_LEFT);
            $width = $columnWidth;
        }

        return "COMMENT:$header\\l";
    }

    /**
     * The legend values of a series, ending the legend line
     *
     * @return array<int, string>
     */
    private function legendValues(string $id, string $unit): array
    {
        $unit = str_replace(['%', ':'], ['%%', '\\:'], $unit);
        $values = [];

        foreach (self::LEGEND_COLUMNS as $column) {
            $values[] = "GPRINT:$id{$column['suffix']}:{$column['cf']}:%5." . self::PRECISION . "lf%s$unit";
        }

        $values[array_key_last($values)] .= '\\n';

        return $values;
    }
}
