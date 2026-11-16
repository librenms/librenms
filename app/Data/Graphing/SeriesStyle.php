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
     * @param  string|null  $maxFillColor  fill the area below the maximum of a line with this color, behind the other fills
     * @param  bool  $invert  draw below the axis
     * @param  float  $drawScale  multiply the drawn value
     * @param  bool  $stackAverage  divide the drawn value by the number of drawn series with this set, so a stack of percentages shows their average
     */
    public function __construct(
        public ?string $color = null,
        public bool $stacked = false,
        public bool $fill = false,
        public ?string $fillColor = null,
        public ?string $maxFillColor = null,
        public bool $invert = false,
        public float $drawScale = 1,
        public bool $stackAverage = false,
    ) {
    }
}
