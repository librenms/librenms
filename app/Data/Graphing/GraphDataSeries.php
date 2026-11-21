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
     *
     * @param  array<string, DataSeries|SeriesSum>  $series
     * @return list<self>
     */
    public static function fromReadings(array $series, SeriesData $data, GraphTheme $theme, bool $invert): array
    {
        $styles = ResolvedStyle::resolve(array_intersect_key($series, $data->series), $theme, $invert);
        $css = fn (?string $hex) => $hex === null ? null : "#$hex";

        $drawn = [];
        foreach ($styles as $key => $style) {
            $drawn[] = new self(
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
        }

        return $drawn;
    }
}
