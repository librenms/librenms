<?php

namespace App\Data\Graphing;

/**
 * How a series is drawn. This only affects the drawing, legends and data always show the series value.
 * Graphs never choose colors: the role says what the series is and the renderer picks colors for it.
 */
readonly class SeriesStyle
{
    /**
     * @param  SeriesRole|null  $role  what the series shows, the colors of the graph palette when null
     * @param  bool  $stacked  draw as an area on top of the previous stacked series, instead of a line.
     *                         Series drawn below the axis stack separately from those drawn above it.
     * @param  bool  $fill  fill the area below a line
     * @param  bool  $maxFill  fill the area below the maximum of a line, behind the other fills
     * @param  bool  $invert  draw below the axis
     * @param  float  $drawScale  multiply the drawn value
     * @param  bool  $stackAverage  divide the drawn value by the number of drawn series with this set, so a stack of percentages shows their average
     * @param  bool  $range  fill the area between the minimum and maximum of a line, behind the other fills
     * @param  bool  $legendOnly  show the values in the legend without drawing the series
     * @param  bool  $rollingAverages  add 1 hour, 1 day and 1 week average lines, see RollingAverage
     * @param  bool  $percentileLines  draw the percentiles of the graph, GraphOptions::$percentiles, as dashed lines of this series
     */
    public function __construct(
        public ?SeriesRole $role = null,
        public bool $stacked = false,
        public bool $fill = false,
        public bool $maxFill = false,
        public bool $invert = false,
        public float $drawScale = 1,
        public bool $stackAverage = false,
        public bool $range = false,
        public bool $legendOnly = false,
        public bool $rollingAverages = false,
        public bool $percentileLines = false,
    ) {
    }
}
