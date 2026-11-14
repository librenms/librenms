<?php

namespace App\Data\TimeSeries;

/**
 * Series share the same points. A point is the average, lowest and highest of the values stored in its interval.
 * Series without data are left out, values are null where there is no data.
 */
final readonly class SeriesData
{
    /**
     * @param  list<int>  $timestamps  unix time each point's interval ends
     * @param  array<string, array{avg: list<float|null>, min: list<float|null>, max: list<float|null>}>  $series  keyed like the requested series
     */
    public function __construct(
        public array $timestamps = [],
        public array $series = [],
    ) {
    }
}
