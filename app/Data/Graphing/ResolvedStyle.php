<?php

namespace App\Data\Graphing;

use App\Facades\LibrenmsConfig;
use App\Graphs\SeriesPalette;

/**
 * How a series is drawn once the graph is known: defaults filled in and the style rules applied.
 * Images and the browser both draw from this, so they look the same.
 */
readonly class ResolvedStyle
{
    /**
     * @param  string  $color  hex without a leading #
     * @param  string|null  $fillColor  hex of the area below the line, or of the stacked area, null for none
     * @param  string|null  $maxFillColor  hex of the area below the maximum of a line, null for none
     * @param  bool  $invert  drawn below the axis
     * @param  float  $drawScale  multiplies the drawn value
     */
    public function __construct(
        public string $color,
        public ?string $fillColor,
        public ?string $maxFillColor,
        public bool $stacked,
        public bool $invert,
        public float $drawScale,
    ) {
    }

    /**
     * Series without a color get the next one of the palette, in order
     *
     * @param  array<string, DataSeries>  $series  the series that are drawn
     * @param  string  $palette  a graph_colours palette
     * @return array<string, self>
     */
    public static function resolve(array $series, string $palette): array
    {
        $colors = new SeriesPalette($palette);
        $inverts = ! LibrenmsConfig::get('webui.graph_stacked');
        $averaged = count(array_filter($series, fn (DataSeries $item) => $item->style->stackAverage));

        return array_map(function (DataSeries $item) use ($colors, $inverts, $averaged): self {
            $style = $item->style;
            $color = $style->color ?? $colors->next();

            return new self(
                color: $color,
                fillColor: $style->stacked ? $color : ($style->fill ? $style->fillColor ?? $color . '20' : null),
                maxFillColor: $style->maxFillColor,
                stacked: $style->stacked,
                invert: $style->invert && $inverts,
                drawScale: $style->stackAverage ? $style->drawScale / $averaged : $style->drawScale,
            );
        }, $series);
    }
}
