<?php

namespace App\Data\Graphing;

use App\Graphs\Rrd\GraphTheme;

/**
 * How a series is drawn in the browser once the graph is known: colors picked by the theme and the style rules applied
 * as RrdSeriesDefinition applies them to images, so both look the same.
 */
readonly class ResolvedStyle
{
    /**
     * @param  string|null  $color  hex without a leading #, null for a legend only series
     * @param  string|null  $fillColor  hex of the area below the line, or of the stacked area, null for none
     * @param  string|null  $maxFillColor  hex of the area below the maximum of a line, null for none
     * @param  string|null  $rangeColor  hex of the area between the minimum and maximum of a line, null for none
     * @param  bool  $invert  drawn below the axis
     * @param  float  $drawScale  multiplies the drawn value
     * @param  bool  $legendOnly  shown in the legend, not drawn
     */
    public function __construct(
        public ?string $color,
        public ?string $fillColor,
        public ?string $maxFillColor,
        public ?string $rangeColor,
        public bool $stacked,
        public bool $invert,
        public float $drawScale,
        public bool $legendOnly = false,
    ) {
    }

    /**
     * Drawn series get the next color of their role, in order
     *
     * @param  array<string, DataSeries|SeriesSum>  $series  the series that are drawn
     * @return array<string, self>
     */
    public static function resolve(array $series, GraphTheme $theme, bool $inverts = true): array
    {
        $averaged = count(array_filter($series, fn (DataSeries|SeriesSum $item) => $item->style->stackAverage));

        return array_map(function (DataSeries|SeriesSum $item) use ($theme, $inverts, $averaged): self {
            $style = $item->style;

            if ($style->legendOnly) {
                return new self(null, null, null, null, false, false, 1, legendOnly: true);
            }

            $color = $theme->next($style->role, $style->stacked);

            return new self(
                color: $color,
                fillColor: $style->stacked ? $color : ($style->fill ? $theme->fill($style->role, $color) : null),
                maxFillColor: $style->maxFill ? $theme->maxFill($style->role, $color) : null,
                rangeColor: $style->range ? $theme->color(SeriesRole::Muted) : null,
                stacked: $style->stacked,
                invert: $style->invert && $inverts,
                drawScale: $style->stackAverage ? $style->drawScale / $averaged : $style->drawScale,
            );
        }, $series);
    }
}
