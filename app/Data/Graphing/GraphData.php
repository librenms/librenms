<?php

namespace App\Data\Graphing;

/**
 * A graph for the browser to draw, sent as JSON with these property names.
 * Keep in step with the GraphData typedef in resources/js/timeSeriesChart.js.
 */
readonly class GraphData
{
    /**
     * @param  list<int>  $timestamps  unix time each point's interval ends
     * @param  array{label: string, min: float|null, max: float|null, base: int}  $axis  the value axis, min and max are limits when set
     * @param  list<GraphDataSeries>  $series
     */
    public function __construct(
        public array $timestamps,
        public array $axis,
        public array $series,
    ) {
    }
}
