<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\Formula;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\ThemeColor;
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
        'now' => ['suffix' => '', 'cf' => 'LAST'],
        'min' => ['suffix' => 'min', 'cf' => 'MIN'],
        'max' => ['suffix' => 'max', 'cf' => 'MAX'],
        'avg' => ['suffix' => '', 'cf' => 'AVERAGE'],
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
        $margins = [];
        $maxFills = [];
        $fills = [];
        $drawing = [];
        $previousValues = [];
        $stacking = [];
        $inverted = false;
        $traffic = $this->options->traffic === null ? null : new RrdTrafficDefinition($this->options->traffic, $params);

        $files = [];
        foreach ($this->series as $key => $series) {
            $filename = $this->rrdFiles->pathFor($series->metric);

            if ($filename !== null) {
                $files[$key] = $filename;
            }
        }

        if ($files === []) {
            throw new NoGraphData(__('graph.errors.no_data_files'));
        }

        $dark = $params->isDark();
        $averaged = count(array_filter(array_keys($files), fn ($key) => $this->series[$key]->style->stackAverage));

        $i = 0;
        foreach ($files as $key => $filename) {
            $series = $this->series[$key];
            $id = 'ds' . $i++;
            $style = $series->style;
            $drawScale = $style->stackAverage ? $style->drawScale / $averaged : $style->drawScale;
            $color = $this->hex($style->color, $dark) ?? $this->nextColor();
            $label = Rrd::fixedSafeDescr($series->description, self::LABEL_WIDTH);
            $inverted = $inverted || $style->invert;

            foreach (['' => 'AVERAGE', 'min' => 'MIN', 'max' => 'MAX'] as $suffix => $cf) {
                array_push($defs, ...self::valueDefs($id . $suffix, $filename, $series, $cf));
            }
            $drawnId = $this->drawnValue($id, $drawScale, $style->invert, $defs);

            if ($style->stacked) {
                // series drawn below the axis stack separately
                $direction = $this->drawnDirection($drawScale, $style->invert);
                $drawing[] = "AREA:$drawnId#$color:$label" . (isset($stacking[$direction]) ? ':STACK' : '');
                $stacking[$direction] = true;

                if ($params->visible('previous')) {
                    array_push($defs, ...self::valueDefs("{$id}prev", $filename, $series, 'AVERAGE', ":start=$params->prev_from:end=$params->from", $params->period));
                    $previousValues[$direction][] = "{$id}prev,UN,0,{$id}prev,IF" . $this->scaleRpn($this->drawnFactor($drawScale, $style->invert));
                }
            } else {
                $drawing[] = "LINE1.25:$drawnId#$color:$label";
                if ($style->fill) {
                    $fills[] = "AREA:$drawnId#" . ($this->hex($style->fillColor, $dark) ?? $color . '20');
                }
                if ($style->maxFillColor !== null) {
                    $maxFills[] = 'AREA:' . $this->drawnValue("{$id}max", $drawScale, $style->invert, $defs) . '#' . $this->hex($style->maxFillColor, $dark);
                }
                if ($this->options->scaleMargin > 0) {
                    // invisible lines that only widen the autoscaled axis
                    $margin = self::number($this->options->scaleMargin);
                    // drawn below the axis, the minimum is the highest drawn value
                    [$top, $bottom] = $this->drawnFactor($drawScale, $style->invert) < 0 ? ['min', 'max'] : ['max', 'min'];
                    foreach (['above' => [$top, '+'], 'below' => [$bottom, '-']] as $name => [$suffix, $operator]) {
                        $value = $this->drawnValue($id . $suffix, $drawScale, $style->invert, $defs);
                        $defs[] = "CDEF:$id$name=$value,$value,ABS,$margin,*,$operator";
                        $margins[] = "LINE:$id$name#00000000";
                    }
                }
                if ($style->rangeColor !== null) {
                    // an invisible line at the minimum, the range stacked on it
                    $min = $this->drawnValue("{$id}min", $drawScale, $style->invert, $defs);
                    $defs[] = "CDEF:{$id}range=" . $this->drawnValue("{$id}max", $drawScale, $style->invert, $defs) . ",$min,-";
                    array_push($maxFills, "LINE:$min#00000000", "AREA:{$id}range#" . $this->hex($style->rangeColor, $dark) . '::STACK');
                }
            }

            array_push($drawing, ...$this->legendValues($id, $series->unit));
            if ($traffic !== null) {
                $traffic->addSeries($key, $id, $filename, $series, $drawScale);
            }
        }

        // the previous period of each stack, below the axis for series drawn below it
        foreach ($previousValues as $direction => $values) {
            $previous = $direction === 'up' ? 'previous' : "previous$direction";
            $defs[] = "CDEF:$previous=" . implode(',', $values) . str_repeat(',+', count($values) - 1);
            array_push($drawing, "AREA:$previous#99999999:", "LINE1.25:$previous#666666:");
        }

        if ($inverted) {
            $drawing[] = 'HRULE:0#555555';
        }

        if ($this->options->thresholds) {
            $last = array_key_last($this->options->thresholds);
            foreach ($this->options->thresholds as $index => $threshold) {
                $label = str_replace(':', '\\:', $threshold->label) . ($index === $last ? '\\l' : '');
                $drawing[] = 'LINE1.5:' . self::number($threshold->value) . "#$threshold->color:$label:dashes";
            }
        }

        // fills go below all lines, so they do not hide lines of other series
        return [$this->legendHeader(), ...$defs, ...($traffic?->definitions() ?? []), ...$margins, ...$maxFills, ...$fills, ...$drawing, ...($traffic?->drawing() ?? [])];
    }

    /**
     * Define $id as one consolidation of the series value, in the units of the series
     *
     * @param  string  $cf  AVERAGE, MIN or MAX
     * @param  string  $range  read options appended to each DEF, such as the start and end of the previous period
     * @param  int|null  $shift  move the values later by this many seconds
     * @return list<string>
     */
    public static function valueDefs(string $id, string $filename, DataSeries $series, string $cf, string $range = '', ?int $shift = null): array
    {
        $transform = ($series->scale == 1 ? '' : ',' . self::number($series->scale) . ',*') . ($series->offset == 0 ? '' : ',' . self::number($series->offset) . ',+');

        if ($series->field instanceof Formula) {
            // the lowest and highest of a formula are those of its average values
            $defs = [];
            $names = [];
            foreach ($series->field->fields() as $index => $field) {
                $names[$field] = "$id{$index}f";
                $defs[] = "DEF:{$names[$field]}=$filename:$field:AVERAGE$range";
                if ($shift !== null) {
                    $defs[] = "SHIFT:{$names[$field]}:$shift";
                }
            }
            $defs[] = "CDEF:$id=" . implode(',', array_map(fn ($token) => is_string($token) ? $names[$token] ?? $token : self::number($token), $series->field->tokens)) . $transform;

            return $defs;
        }

        $field = match ($cf) {
            'MIN' => $series->minField,
            'MAX' => $series->maxField,
            default => null,
        } ?? $series->field;

        if ($transform === '') {
            return $shift === null ? ["DEF:$id=$filename:$field:$cf$range"] : ["DEF:$id=$filename:$field:$cf$range", "SHIFT:$id:$shift"];
        }

        $defs = ["DEF:{$id}raw=$filename:$field:$cf$range"];
        if ($shift !== null) {
            $defs[] = "SHIFT:{$id}raw:$shift";
        }
        $defs[] = "CDEF:$id={$id}raw$transform";

        return $defs;
    }

    /**
     * Apply the draw only transformations of the style
     *
     * @param  string  $id  the value to draw, such as ds0 or ds0max
     * @param  array<int, string>  $defs  receives the definition, if one is needed and not there yet
     * @return string the id to draw
     */
    private function drawnValue(string $id, float $drawScale, bool $invert, array &$defs): string
    {
        $scale = $this->scaleRpn($this->drawnFactor($drawScale, $invert));

        if ($scale === '') {
            return $id;
        }

        $def = "CDEF:{$id}drawn=$id$scale";
        if (! in_array($def, $defs, true)) {
            $defs[] = $def;
        }

        return $id . 'drawn';
    }

    /**
     * Whether a drawn value with these transformations is above or below the axis
     */
    private function drawnDirection(float $drawScale, bool $invert): string
    {
        return $this->drawnFactor($drawScale, $invert) < 0 ? 'down' : 'up';
    }

    /**
     * What drawing multiplies a value by, negative below the axis
     */
    private function drawnFactor(float $drawScale, bool $invert): float
    {
        return $drawScale * ($invert && ! LibrenmsConfig::get('webui.graph_stacked') ? -1 : 1);
    }

    /**
     * RPN suffix multiplying by the scale, empty when there is nothing to do
     */
    private function scaleRpn(float $scale): string
    {
        return $scale == 1 ? '' : ',' . self::number($scale) . ',*';
    }

    /**
     * The hex of a color, of a theme color the variation for the style
     *
     * @return ($color is null ? null : string)
     */
    private function hex(string|ThemeColor|null $color, bool $dark): ?string
    {
        return $color instanceof ThemeColor ? $color->for($dark) : $color;
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
     * A number for rrdtool, which does not read exponents such as 1.2E-5
     */
    public static function number(int|float $number): string
    {
        if (is_int($number) || $number == 0 || ! is_finite($number)) {
            return (string) $number;
        }

        // about 15 significant digits, like PHP
        $decimals = max(0, 14 - (int) floor(log10(abs($number))));

        $fixed = sprintf("%.{$decimals}F", $number);

        return $decimals === 0 ? $fixed : rtrim(rtrim($fixed, '0'), '.');
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

        foreach (array_keys(self::LEGEND_COLUMNS) as $column) {
            $header .= mb_str_pad(__("graph.legend.$column"), $width, ' ', STR_PAD_LEFT);
            $width = $columnWidth;
        }

        if ($this->options->traffic !== null) {
            $header .= mb_str_pad(__('graph.legend.percentile', ['percentile' => $this->options->traffic->percentile]), $columnWidth, ' ', STR_PAD_LEFT);
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
        // the space takes the place of the prefix, so the columns line up either way
        $format = '%5.' . self::PRECISION . 'lf' . ($this->options->unitsExponent === 0 ? ' ' : '%s') . $unit;
        $values = [];

        foreach (self::LEGEND_COLUMNS as $column) {
            $values[] = "GPRINT:$id{$column['suffix']}:{$column['cf']}:$format";
        }

        if ($this->options->traffic !== null) {
            $values[] = "GPRINT:{$id}pct:$format";
        }

        $values[array_key_last($values)] .= '\\n';

        return $values;
    }
}
