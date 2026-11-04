<?php

namespace App\Data\Graphing;

/**
 * How a series is drawn. This only affects the drawing, legends and data always show the series value.
 */
readonly class SeriesStyle
{
    /**
     * @param  string|null  $color  defaults to the next color of the graph palette
     * @param  bool  $stacked  draw as an area on top of the previous stacked series, instead of a line
     * @param  bool  $fill  fill the area below a line
     * @param  string|null  $fillColor  defaults to a transparent version of the line color
     * @param  bool  $invert  draw below the axis
     * @param  float  $drawScale  multiply the drawn value, for example so stacked percentages add up to 100
     */
    public function __construct(
        public ?string $color = null,
        public bool $stacked = false,
        public bool $fill = false,
        public ?string $fillColor = null,
        public bool $invert = false,
        public float $drawScale = 1,
    ) {
    }
}
