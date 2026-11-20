<?php

namespace App\Data\Graphing;

use App\Facades\LibrenmsConfig;

/**
 * How a series is drawn in the browser once the graph is known: defaults filled in and the style rules applied
 * as RrdSeriesDefinition applies them to images, so both look the same.
 */
readonly class ResolvedStyle
{
    /**
     * @param  string|null  $color  hex without a leading #, null for a legend only series without a color
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
     * Drawn series without a color get the next one of the palette, in order, starting over when they run out
     *
     * @param  array<string, DataSeries|SeriesSum>  $series  the series that are drawn
     * @param  string  $palette  a graph_colours palette
     * @param  bool  $dark  theme colors for the dark site style
     * @return array<string, self>
     */
    public static function resolve(array $series, string $palette, bool $dark = false): array
    {
        $colors = LibrenmsConfig::get("graph_colours.$palette");
        $colors = is_array($colors) && $colors !== [] ? array_values($colors) : ['000000'];
        $next = 0;
        $inverts = ! LibrenmsConfig::get('webui.graph_stacked');
        $averaged = count(array_filter($series, fn (DataSeries|SeriesSum $item) => $item->style->stackAverage));
        $hex = fn (string|ThemeColor|null $color): ?string => $color instanceof ThemeColor ? $color->for($dark) : $color;

        return array_map(function (DataSeries|SeriesSum $item) use ($colors, &$next, $inverts, $averaged, $hex): self {
            $style = $item->style;

            if ($style->legendOnly) {
                return new self($hex($style->color), null, null, null, false, false, 1, legendOnly: true);
            }

            $color = $hex($style->color) ?? $colors[$next++ % count($colors)];

            return new self(
                color: $color,
                fillColor: $style->stacked ? $color : ($style->fill ? $hex($style->fillColor) ?? $color . '20' : null),
                maxFillColor: $hex($style->maxFillColor),
                rangeColor: $hex($style->rangeColor),
                stacked: $style->stacked,
                invert: $style->invert && $inverts,
                drawScale: $style->stackAverage ? $style->drawScale / $averaged : $style->drawScale,
            );
        }, $series);
    }
}
