<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\Formula;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesSum;
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

    /** @var array<string, string> rrd file of each data series that has one */
    private array $files = [];

    /** @var array<string, string> value id of each series that is drawn */
    private array $ids = [];

    /**
     * @param  array<string, DataSeries|SeriesSum>  $series
     */
    public function __construct(
        private readonly array $series,
        private readonly GraphOptions $options,
        private readonly RrdFileResolver $rrdFiles,
    ) {
    }

    /**
     * Series without an rrd file are left out, as are sums without any of their series
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
        /** @var array<string, array{top: string|null, pending: list<string>}> $stacks the drawn values of each stack not yet added to its top */
        $stacks = [];
        $lastStack = null;
        $inverted = false;
        $drawable = [];

        $this->files = [];
        $this->ids = [];
        foreach ($this->series as $key => $series) {
            if ($series instanceof DataSeries) {
                $filename = $this->rrdFiles->pathFor($series->metric);
                if ($filename === null) {
                    continue;
                }

                $this->files[$key] = $filename;
            } elseif (! array_any($series->series, fn ($member) => isset($drawable[$member]))) {
                continue;
            }

            $drawable[$key] = $series;
            $inverted = $inverted || ($series->style->invert && ! $series->style->legendOnly);
        }

        if ($this->files === []) {
            throw new NoGraphData(__('graph.errors.no_data_files'));
        }

        $overlaid = self::overlaid($this->options);
        $theme = new GraphTheme($params->isDark(), $this->options->palette, $overlaid && $inverted);
        $traffic = $this->options->traffic === null ? null : new RrdTrafficDefinition($this->options->traffic, $params, $theme);
        $averaged = count(array_filter(array_keys($this->files), fn ($key) => $this->series[$key]->style->stackAverage));

        $i = 0;
        foreach ($drawable as $key => $series) {
            $id = 'ds' . $i++;
            $this->ids[$key] = $id;
            $style = $series->style;
            $drawScale = $style->stackAverage ? $style->drawScale / $averaged : $style->drawScale;
            $factor = $style->invert && ! $overlaid ? -$drawScale : $drawScale;
            $label = Rrd::fixedSafeDescr($series->description, self::LABEL_WIDTH);
            $summarized = $traffic !== null && $this->options->traffic?->summarizes($key);

            foreach (['' => 'AVERAGE', 'min' => 'MIN', 'max' => 'MAX'] as $suffix => $cf) {
                array_push($defs, ...$this->periodValueDefs($key, '', $suffix, $cf));
            }

            $color = null;
            if ($style->legendOnly) {
                // a legend entry that draws nothing
                $drawing[] = "HRULE:0#00000000:$label";
                $lastStack = null;
            } else {
                $color = $theme->next($style->role, $style->stacked);
                $drawnId = $this->drawnValue($id, $factor, $defs);

                if ($style->stacked) {
                    // series drawn below the axis stack separately
                    $direction = $factor < 0 ? 'down' : 'up';

                    if (! isset($stacks[$direction])) {
                        $drawing[] = "AREA:$drawnId#$color:$label";
                        $stacks[$direction] = ['top' => null, 'pending' => []];
                    } else {
                        if ($lastStack !== $direction) {
                            // rrdtool stacks on the previous element, rest on the top of this stack instead
                            $drawing[] = 'LINE:' . $this->stackTop($id, $stacks[$direction], $defs) . '#00000000';
                        }
                        $drawing[] = "AREA:$drawnId#$color:$label:STACK";
                    }
                    $stacks[$direction]['pending'][] = $drawnId;
                    $lastStack = $direction;

                    if ($params->visible('previous')) {
                        array_push($defs, ...$this->periodValueDefs($key, 'prev', '', 'AVERAGE', ":start=$params->prev_from:end=$params->from", $params->period));
                        $previousValues[$direction][] = "{$id}prev,UN,0,{$id}prev,IF" . $this->scaleRpn($factor);
                    }
                } else {
                    $lastStack = null;
                    $drawing[] = "LINE1.25:$drawnId#$color:$label";
                    if ($style->fill) {
                        $fills[] = "AREA:$drawnId#" . $theme->fill($style->role, $color);
                    }
                    if ($style->maxFill) {
                        $maxFills[] = 'AREA:' . $this->drawnValue("{$id}max", $factor, $defs) . '#' . $theme->maxFill($style->role, $color);
                    }
                    if ($this->options->scaleMargin > 0) {
                        // invisible lines that only widen the autoscaled axis
                        $margin = self::number($this->options->scaleMargin);
                        // drawn below the axis, the minimum is the highest drawn value
                        [$top, $bottom] = $factor < 0 ? ['min', 'max'] : ['max', 'min'];
                        foreach (['above' => [$top, '+'], 'below' => [$bottom, '-']] as $name => [$suffix, $operator]) {
                            $value = $this->drawnValue($id . $suffix, $factor, $defs);
                            $defs[] = "CDEF:$id$name=$value,$value,ABS,$margin,*,$operator";
                            $margins[] = "LINE:$id$name#00000000";
                        }
                    }
                    if ($style->range) {
                        // an invisible line at the minimum, the range stacked on it
                        $min = $this->drawnValue("{$id}min", $factor, $defs);
                        $defs[] = "CDEF:{$id}range=" . $this->drawnValue("{$id}max", $factor, $defs) . ",$min,-";
                        array_push($maxFills, "LINE:$min#00000000", "AREA:{$id}range#" . $theme->color(SeriesRole::Muted) . '::STACK');
                    }
                }
            }

            array_push($drawing, ...$this->legendValues($id, $series->unit, $summarized));
            if ($summarized) {
                $traffic->addSeries($id, $series, $factor, $color, fn (string $period, string $suffix, string $cf, string $range, int $shift) => $this->periodValueDefs($key, $period, $suffix, $cf, $range, $shift));
            }
        }

        // the previous period of each stack, below the axis for series drawn below it
        foreach ($previousValues as $direction => $values) {
            $previous = $direction === 'up' ? 'previous' : "previous$direction";
            $defs[] = "CDEF:$previous=" . implode(',', $values) . str_repeat(',+', count($values) - 1);
            array_push($drawing, "AREA:$previous#{$theme->previousStackArea()}:", "LINE1.25:$previous#{$theme->previousStackLine()}:");
        }

        if ($inverted) {
            $drawing[] = 'HRULE:0#' . $theme->axis();
        }

        $thresholds = $this->options->shownThresholds();
        if ($thresholds) {
            // invisible lines that keep the thresholds inside an autoscaled axis, away from its edges
            foreach ($this->options->thresholdBounds(! $overlaid) ?? [] as $bound) {
                $margins[] = 'LINE:' . self::number($bound) . '#00000000';
            }

            $last = array_key_last($thresholds);
            foreach ($thresholds as $index => $threshold) {
                $label = str_replace(':', '\\:', $threshold->label) . ($index === $last ? '\\l' : '');
                // a mirrored line is in the legend once
                foreach ($threshold->drawnValues(! $overlaid) as $line => $value) {
                    $drawing[] = 'LINE1.5:' . self::number($value) . '#' . $theme->color($threshold->role) . ':' . ($line === 0 ? $label : '') . ':dashes';
                }
            }
        }

        // a value used by several series is defined once
        $defs = array_values(array_unique([...$defs, ...($traffic?->definitions() ?? [])]));

        // fills go below all lines, so they do not hide lines of other series
        return [$this->legendHeader(), ...$defs, ...$margins, ...$maxFills, ...$fills, ...$drawing, ...($traffic?->drawing() ?? [])];
    }

    /**
     * Define the value of a drawn series as its id followed by $period and $suffix, a sum from the values of its series
     *
     * @param  string  $period  names the period, empty for the period of the graph
     * @param  string  $cf  AVERAGE, MIN or MAX, the lowest and highest of a sum are those of its averages
     * @param  string  $range  read options appended to each DEF, such as the start and end of the previous period
     * @param  int|null  $shift  move the values later by this many seconds
     * @return list<string>
     */
    private function periodValueDefs(string $key, string $period, string $suffix, string $cf, string $range = '', ?int $shift = null): array
    {
        $series = $this->series[$key];
        $id = $this->ids[$key] . $period . $suffix;

        if ($series instanceof DataSeries) {
            return self::valueDefs($id, $this->files[$key], $series, $cf, $range, $shift);
        }

        $defs = [];
        $values = [];
        foreach ($series->series as $member) {
            if (isset($this->ids[$member])) {
                array_push($defs, ...$this->periodValueDefs($member, $period, '', 'AVERAGE', $range, $shift));
                $values[] = $this->ids[$member] . $period;
            }
        }
        $defs[] = self::sumDef($id, $values);

        return $defs;
    }

    /**
     * Define the top of a stack, where the next series of the stack starts
     *
     * @param  array{top: string|null, pending: list<string>}  $stack  the drawn values not yet added to the top, which this adds
     * @param  array<int, string>  $defs  receives the definition
     * @return string the id of the top
     */
    private function stackTop(string $id, array &$stack, array &$defs): string
    {
        // unknown values add nothing to the stack, as when rrdtool stacks them
        $values = [$stack['top'] ?? '0', ...$stack['pending']];
        $defs[] = self::sumDef($id . 'base', $values);
        $stack = ['top' => "{$id}base", 'pending' => []];

        return "{$id}base";
    }

    /**
     * Sum the available members at each timestamp. A completely missing sample stays unknown.
     *
     * @param  list<string>  $members
     */
    public static function sumDef(string $id, array $members): string
    {
        return "CDEF:$id=" . implode(',', $members) . str_repeat(',ADDNAN', count($members) - 1);
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

        $source = $transform === '' ? $id : $id . 'raw';
        $defs = ["DEF:$source=$filename:$field:$cf$range"];
        if ($shift !== null) {
            $defs[] = "SHIFT:$source:$shift";
        }
        if ($transform !== '') {
            $defs[] = "CDEF:$id=$source$transform";
        }

        return $defs;
    }

    /**
     * Apply the draw only transformations of the style
     *
     * @param  string  $id  the value to draw, such as ds0 or ds0max
     * @param  array<int, string>  $defs  receives the definition, if one is needed and not there yet
     * @return string the id to draw
     */
    private function drawnValue(string $id, float $factor, array &$defs): string
    {
        $scale = $this->scaleRpn($factor);

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
     * RPN suffix multiplying by the scale, empty when there is nothing to do
     */
    private function scaleRpn(float $scale): string
    {
        return $scale == 1 ? '' : ',' . self::number($scale) . ',*';
    }

    /**
     * Stacked graphs and graphs zoomed to the port speed draw series marked invert above the axis, over the others
     */
    public static function overlaid(GraphOptions $options): bool
    {
        return LibrenmsConfig::get('webui.graph_stacked') || ($options->traffic->speedZoom ?? false);
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
        $unitWidth = max([0, ...array_map(fn (DataSeries|SeriesSum $series) => strlen($series->unit), $this->series)]);
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
    private function legendValues(string $id, string $unit, bool $summarized): array
    {
        $unit = str_replace(['%', ':'], ['%%', '\\:'], $unit);
        // the space takes the place of the prefix, so the columns line up either way
        $format = '%5.' . self::PRECISION . 'lf' . ($this->options->unitsExponent === 0 ? ' ' : '%s') . $unit;
        $values = [];

        foreach (self::LEGEND_COLUMNS as $column) {
            $values[] = "GPRINT:$id{$column['suffix']}:{$column['cf']}:$format";
        }

        if ($summarized) {
            $values[] = "GPRINT:{$id}pct:$format";
        }

        $values[array_key_last($values)] .= '\\n';

        return $values;
    }
}
