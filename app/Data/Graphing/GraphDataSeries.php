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
    ) {
    }

    /**
     * Apply the graph's styles in definition order, skipping series without an RRD file.
     * The rolling averages and percentiles of a series follow it as lines of their own.
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
            );

            $drawn[] = $item;
            if (! $style->legendOnly) {
                array_push($drawn, ...self::statsOf($item, SeriesStats::of($series[$key]->style, $theme, $range), $data->timestamps));
            }
        }

        return $drawn;
    }

    /**
     * Lines of the rolling averages and percentiles of a series, computed from its points.
     * A rolling average point is the average of the points in its period, so a period partly before the graph averages only the part inside it.
     *
     * @param  list<int>  $timestamps
     * @return list<self>
     */
    private static function statsOf(self $base, SeriesStats $stats, array $timestamps): array
    {
        $line = fn (string $name, string $label, string $color, array $avg) => new self(
            key: "$base->key.$name",
            label: $label,
            unit: $base->unit,
            color: "#$color",
            fillColor: null,
            maxFillColor: null,
            rangeColor: null,
            legendOnly: false,
            stacked: false,
            invert: $base->invert,
            drawScale: $base->drawScale,
            avg: $avg,
            min: null,
            max: null,
        );
        $lines = [];

        foreach ($stats->averages as $name => ['label' => $label, 'step' => $step, 'color' => $color]) {
            // rrdtool consolidates into periods ending at multiples of the step, a point is in the period its interval ends in
            $periodOf = fn (int $time) => intdiv($time + $step - 1, $step);
            $periods = [];
            foreach ($timestamps as $index => $time) {
                if ($base->avg[$index] !== null) {
                    $periods[$periodOf($time)][] = $base->avg[$index];
                }
            }
            $averages = array_map(fn (array $values) => array_sum($values) / count($values), $periods);

            $lines[] = $line($name, $label, $color, array_map(fn (int $time) => $averages[$periodOf($time)] ?? null, $timestamps));
        }

        $values = array_values(array_filter($base->avg, fn (?float $value) => $value !== null));
        if ($values === []) {
            return $lines;
        }

        sort($values);
        foreach ($stats->percentiles as $index => ['percentile' => $percentile, 'label' => $label, 'color' => $color]) {
            // nearest rank, as the 95th percentile of the browser legend
            $value = $values[max(0, (int) ceil(count($values) * $percentile / 100) - 1)];
            $lines[] = $line("p$index", $label, $color, array_fill(0, count($timestamps), $value));
        }

        return $lines;
    }
}
