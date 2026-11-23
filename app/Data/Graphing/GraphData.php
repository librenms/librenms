<?php

namespace App\Data\Graphing;

/**
 * A graph for the browser to draw, sent as JSON with these property names.
 * Keep in step with the GraphData typedef in resources/js/graphs/seriesData.js.
 */
readonly class GraphData
{
    /**
     * @param  list<int>  $timestamps  unix time each point's interval ends
     * @param  array{label: string, min: float|null, max: float|null, base: int, margin: float}  $axis  the value axis, min and max are limits when set.
     *                                                                                                  Without limits, margin is the fraction of the data range kept free above and below it.
     * @param  list<GraphDataSeries>  $series
     * @param  list<int|float>  $percentiles  a legend column of each for every series, series with a percentileColor also draw them
     * @param  list<array{value: float, label: string, color: string, shown: bool, invert: bool, mirror: bool}>  $thresholds  reference lines, such as alert limits, colors are CSS hex colors.
     *                                                                                                                        Shown ones are drawn until the viewer hides them, the others until the viewer shows them.
     */
    public function __construct(
        public array $timestamps,
        public array $axis,
        public array $series,
        public array $thresholds = [],
        public array $percentiles = [],
    ) {
    }
}
