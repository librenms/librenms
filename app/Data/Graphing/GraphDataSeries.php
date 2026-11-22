<?php

namespace App\Data\Graphing;

use App\Data\TimeSeries\SeriesData;
use App\Graphs\Rrd\GraphTheme;

/**
 * A series of GraphData, its values line up with the timestamps.
 * Colors are CSS hex colors.
 */
readonly class GraphDataSeries
{
    /**
     * @param  string|null  $color  null for a legend only series without a color
     * @param  string|null  $rangeColor  the area between the min and max of a line
     * @param  bool  $legendOnly  shown in the legend, not drawn
     * @param  string|null  $percentileColor  the percentile lines of the graph are drawn for this series in this color, none when null
     * @param  list<float|null>  $avg  null where there is no data
     * @param  list<float|null>|null  $min  null when the same as avg, as it is when each point is a single stored value
     * @param  list<float|null>|null  $max  null when the same as avg
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $unit,
        public ?string $color,
        public ?string $fillColor,
        public ?string $maxFillColor,
        public ?string $rangeColor,
        public bool $legendOnly,
        public bool $stacked,
        public bool $invert,
        public float $drawScale,
        public array $avg,
        public ?array $min,
        public ?array $max,
        public ?string $percentileColor = null,
    ) {
    }

    /**
     * Apply the graph's styles in definition order, skipping series without an RRD file.
     * The rolling averages of a series follow it as series of their own.
     *
     * @param  array<string, DataSeries|SeriesSum>  $series
     * @param  int  $range  seconds covered by the graph
     * @return list<self>
     */
    public static function fromReadings(array $series, SeriesData $data, GraphTheme $theme, bool $invert, int $range): array
    {
        $styles = ResolvedStyle::resolve(array_intersect_key($series, $data->series), $theme, $invert);
        $css = fn (?string $hex) => $hex === null ? null : "#$hex";

        $drawn = [];
        foreach ($styles as $key => $style) {
            $item = new self(
                key: $key,
                label: $series[$key]->description,
                unit: $series[$key]->unit,
                color: $css($style->color),
                fillColor: $css($style->fillColor),
                maxFillColor: $css($style->maxFillColor),
                rangeColor: $css($style->rangeColor),
                legendOnly: $style->legendOnly,
                stacked: $style->stacked,
                invert: $style->invert,
                drawScale: $style->drawScale,
                avg: $data->series[$key]['avg'],
                min: $data->series[$key]['min'],
                max: $data->series[$key]['max'],
                percentileColor: $css($style->percentileColor),
            );

            $drawn[] = $item;
            if (! $style->legendOnly) {
                $averages = RollingAverage::forStyle($series[$key]->style, $theme, $range);
                array_push($drawn, ...array_map(fn (RollingAverage $average) => self::rollingAverage($item, $average, $data->timestamps), $averages));
            }
        }

        return $drawn;
    }

    /**
     * A rolling average of a series, each point the average of the points in its period,
     * so a period partly before the graph averages only the part inside it
     *
     * @param  list<int>  $timestamps
     */
    private static function rollingAverage(self $base, RollingAverage $average, array $timestamps): self
    {
        // rrdtool consolidates into periods ending at multiples of the step, a point is in the period its interval ends in
        $periodOf = fn (int $time) => intdiv($time + $average->step - 1, $average->step);
        $periods = [];
        foreach ($timestamps as $index => $time) {
            if ($base->avg[$index] !== null) {
                $periods[$periodOf($time)][] = $base->avg[$index];
            }
        }
        $averages = array_map(fn (array $values) => array_sum($values) / count($values), $periods);

        return new self(
            key: "$base->key.$average->name",
            label: $average->label,
            unit: $base->unit,
            color: "#$average->color",
            fillColor: null,
            maxFillColor: null,
            rangeColor: null,
            legendOnly: false,
            stacked: false,
            invert: $base->invert,
            drawScale: $base->drawScale,
            avg: array_map(fn (int $time) => $averages[$periodOf($time)] ?? null, $timestamps),
            min: null,
            max: null,
        );
    }
}
